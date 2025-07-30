from typing import List, Optional
from copy import copy
from json import loads
from ots.pricing.abstract_price_search import AbstractOffer
from ots.search.best_price_selector.best_price_selector import BestPriceSelector
from ots.common.config import Config
from ots.common.usage_request_handler import UsageRequestHandler
from ots.price_modifier.price_modifier import PriceModifier
from ots.pricing.rule_calculator import RuleCalculator
from ots.search.best_price_selector.price_option_reconnector import PriceOptionReconnector
from stylers.date_helpers import *
from stylers.utils import execute_cached_query
from datetime import datetime


def transpose_list_of_dicts(a, aggregate):
    # returns dict of lists
    """
    from:
    [
        { 'b/b' : 'bbo1', 'h/b' : 'hbo1'},
        { 'b/b' : 'bbo2', 'h/b' : 'hbo2'}
    ]
    returns:
    {
        'b/b': ['bbo1', 'bbo2'],
        'h/b': ['hbo1', 'hbo2']
    }

    """
    pairs = [(idx, values) for element in a for idx, values in element.iteritems()]

    final = {}
    for grp, value in pairs:
        final[grp] = aggregate(value, final, grp)

    return final


def prepare_for_best_price_search(range_prices):
    # type: (AbstractOffer) -> dict
    """
    returns a more usable structure for available discount combination options like:
    {
    'dr1': {'1': {'options': [option1,option2], 'usage': ...}, '2': {'options': [option3], 'usage': ...}}
    'dr2': {'1': {'options': [option4,option5], 'usage': ...}, '2': {'options': [option6], 'usage': ...}}
    }
    """

    create_option_dict = lambda option: {
        # type: (AbstractOffer) -> dict
        price["meal_plan_id"]: {"options": price, "usages": option.usages} for price in option.prices
    }
    
    format_options = lambda options: [create_option_dict(option) for option in options]

    extend_options = lambda value, final, grp: {
        "options": final.setdefault(grp, {"options": []})["options"] + [value["options"]],
        "usages": value["usages"],
    }

    make_transposed_options = lambda options: transpose_list_of_dicts(
        format_options(options), aggregate=extend_options
    )

    return {date_range: make_transposed_options(options) for date_range, options in range_prices.iteritems()}


class PriceOptionsStruct:
    """
    This class provides a set of iterators for the price options dictionary
    """

    @staticmethod
    def for_meal_plan(options, meal_plan_id):
        """
        :returns an iterator for the price_options filtered for the given meal_plan
        :param options: the price options structure
        :param meal_plan_id: the str meal_plan_id
        """
        for date_range, options_per_meal_plan in options.iteritems():
            yield (date_range, options_per_meal_plan[meal_plan_id])

    @staticmethod
    def meal_plan_ids(options):
        """
        :returns an iterator for the meal_plan_ids
        :param options: the price options structure
        """
        first_date_range_pair = options.iteritems().next()
        iter_meal_plans = first_date_range_pair[1].iterkeys()
        return iter_meal_plans


class AbstractSearch(object):
    def __init__(self, **keyword_parameters):
        self.plpy = keyword_parameters.get("plpy")
        self.organization_id = keyword_parameters.get("organization_id")

        params = loads(keyword_parameters.get("params", "{}"))

        self.request = params.get("request", {})
        interval = params.get("interval", {})
        self.from_date = interval.get("date_from")
        self.from_time = datestr_to_datetime(self.from_date)
        self.to_date = interval.get("date_to")
        self.to_time = (
            None
            if datestr_to_datetime(self.to_date) is None
            else datestr_to_datetime(self.to_date) + timedelta(hours=23, minutes=59, seconds=59)
        )
        self.booking_time = datestr_to_datetime(params.get("booking_date")) or datetime.now()
        self.wedding_time = datestr_to_datetime(params.get("wedding_date"))

        self.returning_client = params.get("returning_client", False)
        self.display_margin = params.get("display_margin", False)

        self.is_interval_query = self.from_date is not None and self.to_date is not None

        self.cart_summary = params.get("cart_summary")
        self.show_inactive = params.get("show_inactive", False)

        self.taxonomies = self._get_all_taxonomies()

        self.settings = {
            "discount_calculations_base": self._get_organization_classification_taxonomy(
                Config.PRICE_MODIFIER_CALCULATIONS_BASE_TX_ID
            ),
            "merged_free_nights": self._get_organization_classification_taxonomy(
                Config.MERGED_FREE_NIGHTS_TX_ID
            ),
        }
        from ots.price_modifier.switch_wrapper import SwitchWrapper
        from ots.price_modifier.rule_wrapper import RuleWrapper
        from ots.price_modifier.combination_wrapper import CombinationWrapper
        from ots.pricing.abstract_price_search import AbstractPriceSearch

        self.price_modifiers = []
        self.date_ranges = {}
        self.request_handler = None # type Optional[RequestHandler]
        self.price_search = None # type: Optional[AbstractPriceSearch]
        self.meal_plans = {}
        self.switch_wrappers = [] # type: List[SwitchWrapper]
        self.combination_wrappers = [] # type: List[CombinationWrapper]
        self.rule_wrappers = [] # type: List[RuleWrapper]

    def _load_request_handler(self, age_rangeable_type, age_rangeable_id):
        """
        Converts request for search request
        """
        self.request_handler = UsageRequestHandler(self.plpy, age_rangeable_type, age_rangeable_id)
        self.request_handler.set_request(self.request)
        return self

    def _load_meal_plans(self, meal_planable_type, meal_planable_id):
        sql = (
            """
            SELECT
                "meal_plans"."id" AS "meal_plan_id",
                "taxonomies"."name" AS "meal_plan_name"
            FROM "model_meal_plans"
            INNER JOIN "meal_plans" ON "model_meal_plans"."meal_plan_id" = "meal_plans"."id"
            INNER JOIN "taxonomies" ON "meal_plans"."name_taxonomy_id" = "taxonomies"."id"
            WHERE
                "model_meal_plans"."meal_planable_type" = \'"""
            + str(meal_planable_type)
            + """\' AND
                "model_meal_plans"."meal_planable_id" = """
            + str(meal_planable_id)
            + """ AND
                "model_meal_plans"."deleted_at" IS NULL AND
                "meal_plans"."deleted_at" IS NULL AND
                "taxonomies"."deleted_at" IS NULL
        """
        )
        result = execute_cached_query(self.plpy, sql)
        self.meal_plans = {}
        for row in result:
            self.meal_plans[str(row["meal_plan_id"])] = row["meal_plan_name"]

    def _get_price_modifiers(
        self, price_modifiable_type, price_modifiable_id, date_ranges, available_devices, age_resolver
    ):
        """
        Creates price_modifier objects from available price_modifiers
        """
        price_modifiers = {
            Config.PRICE_MODIFIER_TYPE_SWITCH: [],
            Config.PRICE_MODIFIER_TYPE_DISCOUNT: [],
            Config.PRICE_MODIFIER_TYPE_RULE: [],
        }

        prodble_type = "App\\ShipGroup" if price_modifiable_type == "App\\ShipGroup" else None
        prodble_id = price_modifiable_id if price_modifiable_type == "App\\ShipGroup" else None

        for date_range in date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER]:
            if not len(date_range["price_modifier_ids"]):
                continue
            price_modifier_sql = (
                """
                SELECT 
                "price_modifiers"."id", 
                "price_modifiers"."name_description_id", 
                "price_modifiers"."modifier_type_taxonomy_id", 
                "price_modifiers"."condition_taxonomy_id", 
                "price_modifiers"."offer_taxonomy_id", 
                "price_modifiers"."priority", 
                "price_modifiers"."description_description_id", 
                "condition_tx"."parent_id" AS "application_level_taxonomy_id",
                -- --
                (
           SELECT array_to_json(array_agg(row_to_json(d)))
           FROM (
                  SELECT
                    price_modifier_metas.taxonomy_id,
                    price_modifier_metas.value
                  FROM price_modifier_metas
                  WHERE price_modifier_metas.price_modifier_id = price_modifiers.id AND
                        price_modifier_metas.deleted_at IS NULL
                ) d
         ) AS type_metas,
         (
           SELECT array_to_json(array_agg(row_to_json(d)))
           FROM (
                  SELECT value_taxonomy_id
                  FROM price_modifier_classifications
                  WHERE price_modifier_classifications.price_modifier_id = price_modifiers.id AND
                        price_modifier_classifications.deleted_at IS NULL
                ) d
         ) AS type_classifications,
         (
           SELECT array_to_json(array_agg(row_to_json(d)))
           FROM (
                  SELECT
                    taxonomy_id,
                    value
                  FROM offer_metas
                  WHERE price_modifier_id = price_modifiers.id AND deleted_at IS NULL
                ) d
         ) AS offer_metas,
         (
           SELECT array_to_json(array_agg(row_to_json(d)))
           FROM (
                  SELECT value_taxonomy_id
                  FROM offer_classifications
                  WHERE price_modifier_id = price_modifiers.id AND deleted_at IS NULL
                ) d
         ) AS offer_classifications
                -- --
                FROM "price_modifiers"
                INNER JOIN "taxonomies" AS "condition_tx" ON "condition_tx"."id" = "price_modifiers"."condition_taxonomy_id"
                WHERE "price_modifiers"."id" IN("""
                + ",".join(str(x) for x in date_range["price_modifier_ids"])
                + """) 
                AND "price_modifiers"."deleted_at" IS NULL
                AND "price_modifiers"."is_active"
                ORDER BY "price_modifiers"."priority" ASC
            """
            )
            price_modifiers_data = execute_cached_query(self.plpy, price_modifier_sql)

            date_range_from_time = datetime.strptime(date_range["from_time"], "%Y-%m-%d %H:%M:%S")
            date_range_to_time = datetime.strptime(date_range["to_time"], "%Y-%m-%d %H:%M:%S") + timedelta(
                days=1
            )

            for price_modifier_data in price_modifiers_data:
                price_modifier = PriceModifier(
                    plpy=self.plpy,
                    request=self.request,
                    properties=price_modifier_data,
                    from_time=date_range_from_time,
                    to_time=date_range_to_time,
                    date_ranges=date_ranges,
                    price_modifiable_type=price_modifiable_type,
                    price_modifiable_id=price_modifiable_id,
                    abstract_search=self,
                    age_resolver=age_resolver,
                    cart_summary=self.cart_summary,
                    request_from_time=self.from_time,
                    request_to_time=self.to_time,
                    available_devices=available_devices,
                    combination_from_time=date_range_from_time,
                    combination_to_time=date_range_to_time,
                )
                if price_modifier.is_applicable(prodble_type, prodble_id):
                    price_modifiers[price_modifier_data["modifier_type_taxonomy_id"]].append(price_modifier)

        return price_modifiers

    def _get_open_and_price_modifier_date_ranges(self, date_rangeable_type, date_rangeable_id):
        """
        Load 'open' and 'price_modifier' date ranges
        """
        date_ranges = {}
        if self.is_interval_query:
            date_ranges[Config.DATE_RANGE_TYPE_OPEN] = self._get_date_ranges(
                date_rangeable_type,
                date_rangeable_id,
                self.from_date,
                self.to_date,
                Config.DATE_RANGE_TYPE_OPEN,
            )
            date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER] = self._get_date_ranges(
                date_rangeable_type,
                date_rangeable_id,
                self.from_date,
                self.to_date,
                Config.DATE_RANGE_TYPE_PRICE_MODIFIER,
            )
        return date_ranges

    def _get_date_ranges(self, date_rangeable_type, date_rangeable_id, from_time, to_time, type_taxonomy_id):
        """
        Queries date ranges by organization, date interval, and type
        In price_modifier query it connects available price_modifiers too
        """
        fields = """
            "date_ranges"."id",
            "date_ranges"."type_taxonomy_id",
            "date_ranges"."from_time",
            "date_ranges"."to_time",
            "date_ranges"."minimum_nights",
            "date_ranges"."name_description_id",
            "date_ranges"."margin_type_taxonomy_id",
            "date_ranges"."margin_value"
        """
        join = ""
        if type_taxonomy_id == Config.DATE_RANGE_TYPE_PRICE_MODIFIER:
            join = """
                INNER JOIN "price_modifier_periods"
                ON
                    "date_ranges"."id" = "price_modifier_periods"."date_range_id" AND
                    "price_modifier_periods"."deleted_at" IS NULL
                INNER JOIN "price_modifiers"
                ON 
                    "price_modifiers"."id" = "price_modifier_periods"."price_modifier_id" AND
                    "price_modifiers"."deleted_at" IS NULL
            """
            fields += ', array_agg("price_modifier_periods"."price_modifier_id" ORDER BY "price_modifier_periods"."price_modifier_id") AS "price_modifier_ids"'

        sql = """
            SELECT {fields}
            FROM "date_ranges"
            {join}
            WHERE
                "date_ranges"."date_rangeable_type" = \'{date_rangeable_type}\'
                AND "date_ranges"."date_rangeable_id" = {date_rangeable_id} 
                AND "date_ranges"."type_taxonomy_id" = {type_taxonomy_id} 
                AND ("date_ranges"."from_time", "date_ranges"."to_time") 
                    OVERLAPS (DATE \'{from_time}\', DATE \'{to_time}\') 
                AND "date_ranges"."deleted_at" IS NULL
            GROUP BY "date_ranges"."id"
            ORDER BY "from_time" ASC
        """.format(
            fields=fields,
            join=join,
            date_rangeable_type=date_rangeable_type,
            date_rangeable_id=str(date_rangeable_id),
            type_taxonomy_id=str(type_taxonomy_id),
            from_time=str(from_time),
            to_time=str(to_time),
        )
        result = execute_cached_query(self.plpy, sql)

        date_ranges = []
        for row in result:
            row["from_time"] = str(row["from_time"])
            row["to_time"] = str(datetime.strptime(str(row["to_time"]), "%Y-%m-%d %H:%M:%S"))
            date_ranges.append(row)

        return date_ranges

    def _get_organization_classification_taxonomy(self, taxonomy_id):
        results = execute_cached_query(
            self.plpy,
            """
            SELECT value_taxonomy_id
            FROM organization_classifications
            WHERE organization_id = """
            + str(self.organization_id)
            + """ AND
            classification_taxonomy_id = """
            + str(taxonomy_id)
            + """ AND
            deleted_at IS NULL
            LIMIT 1
        """,
        )

        if not results:
            raise Exception(
                "Missing classification TX#"
                + str(taxonomy_id)
                + " in organization #"
                + str(self.organization_id)
            )

        value_id = results[0]["value_taxonomy_id"]

        return self.taxonomies[str(value_id)]

    def _get_all_taxonomies(self):
        result = execute_cached_query(
            self.plpy, "SELECT id, name FROM taxonomies WHERE deleted_at IS NULL", True
        )
        taxonomies = {}
        for row in result:
            taxonomies[str(row["id"])] = row["name"]
        return taxonomies

    def _calculate_productable_prices(self, order_itemable_index, productable_id):
        """
        Calculate prices for one productable and one request
        """
        usage_prices = []

        has_switch_wrappers = self.switch_wrappers is not None and len(self.switch_wrappers)
        has_combination_wrappers = self.combination_wrappers is not None and len(self.combination_wrappers)
        # calculate price without price modifier
        usage_prices.append(
            self.price_search.find(
                order_itemable_index,
                self.request_handler.request[order_itemable_index],
                productable_id,
                None,
                self.date_ranges[Config.DATE_RANGE_TYPE_OPEN],
            )
        )
        if usage_prices[0].prices is None:
            return usage_prices[0]

        # calculate prices with switches
        if has_switch_wrappers and not has_combination_wrappers:
            switch_prices = []
            if self.price_search is None:
                raise ValueError("self.price_search is None")
            
            switch_price = self.price_search.find(
                order_itemable_index,
                self.request_handler.request[order_itemable_index],
                productable_id,
                self.switch_wrappers,
                self.date_ranges[Config.DATE_RANGE_TYPE_OPEN],
            )
            if switch_price.prices:
                switch_prices.append(switch_price)

            # if we have any modified price, we don't need original price
            if switch_prices:
                usage_prices = switch_prices

        # calculate prices with discounts
        if has_combination_wrappers:
            combination_prices = []
            for combination_wrapper in self.combination_wrappers:
                combination_price = self.price_search.find(
                    order_itemable_index,
                    self.request_handler.request[order_itemable_index],
                    productable_id,
                    combination_wrapper,
                    self.date_ranges[Config.DATE_RANGE_TYPE_OPEN],
                    self.switch_wrappers if len(self.switch_wrappers) else None,
                    self.rule_wrappers if len(self.rule_wrappers) else None,
                )
                if combination_price.prices:
                    combination_prices.append(combination_price)

            # if we have any modified price, we don't need original price
            if combination_prices:
                usage_prices = combination_prices
        return usage_prices

    @staticmethod
    def _best_productable_prices(productable_prices, productable_field_name, productable_id):
        """
        Return with the best productable_prices
        """

        best_productable_prices = {productable_field_name: productable_id, "prices": []}
        range_productable_prices = AbstractSearch._sort_productable_prices_to_ranges(productable_prices)

        price_option_reconnector = PriceOptionReconnector()
        best_price_selector = BestPriceSelector(price_option_reconnector)

        best_prices_of_range_and_meal_plan = AbstractSearch.get_best_prices_of_data_range_and_meal_plan(
            range_productable_prices, best_price_selector=best_price_selector
        )

        best_prices_for_meal_plan = {}
        for range_name in best_prices_of_range_and_meal_plan.keys():
            for meal_plan_id in best_prices_of_range_and_meal_plan[range_name].keys():
                offer = best_prices_of_range_and_meal_plan[range_name][meal_plan_id]
                if meal_plan_id not in best_prices_for_meal_plan:
                    best_prices_for_meal_plan[meal_plan_id] = offer
                else:
                    best_prices_for_meal_plan[meal_plan_id]["prices"] = AbstractSearch._merge_offers(
                        best_prices_for_meal_plan[meal_plan_id]["prices"], offer["prices"]
                    )

        meal_plan_counter = {}
        for bp in best_prices_of_range_and_meal_plan.values():
            for mp in bp.keys():
                meal_plan_counter[mp] = meal_plan_counter.get(mp, 0) + 1

        active_meal_plans = []
        for mp, mp_count in meal_plan_counter.iteritems():
            if mp_count == len(best_prices_of_range_and_meal_plan.keys()):
                active_meal_plans.append(mp)

        active_meal_plans = sorted(active_meal_plans)
        for key in active_meal_plans:
            best_productable_prices["prices"].append(best_prices_for_meal_plan[key]["prices"])
            range_name = best_prices_of_range_and_meal_plan.keys()[0]
            best_productable_prices["usages"] = best_prices_of_range_and_meal_plan[range_name][key]["usages"]

        return best_productable_prices

    @staticmethod
    def get_best_prices_of_data_range_and_meal_plan(range_productable_prices, best_price_selector):

        options = prepare_for_best_price_search(range_productable_prices)

        best_prices = {}
        for meal_plan_id in PriceOptionsStruct.meal_plan_ids(options):
            options_for_meal_plan = PriceOptionsStruct.for_meal_plan(options, meal_plan_id)
            best_prices_for_meal_plan = best_price_selector.select(options_for_meal_plan)

            # format back to the original structure
            for date_range, best_price in zip(options.keys(), best_prices_for_meal_plan):
                best_prices.setdefault(date_range, {}).setdefault(
                    meal_plan_id, {"prices": best_price["option"], "usages": best_price["usages"]}
                )

        return best_prices

    @staticmethod
    def _sort_productable_prices_to_ranges(productable_prices):
        range_productable_prices = {}
        for productable_price in productable_prices:
            if not productable_price.prices:
                continue
            period = productable_price.prices[0]["period"]
            key = str(period["date_from"]) + "-" + str(period["date_to"])
            range_productable_prices.setdefault(key, []).append(productable_price)
        return range_productable_prices

    @staticmethod
    def _is_price_better(price, compared_price):
        discounted_price_better = float(price["discounted_price"]) > float(compared_price["discounted_price"])

        same_price_with_fewer_discounts = float(price["discounted_price"]) == float(
            compared_price["discounted_price"]
        ) and len(price["discounts"]) < len(compared_price["discounts"])
        real_discounted_price = float(copy(compared_price["original_price"]))
        rule_value = 0
        for modifier in compared_price["discounts"]:
            if modifier["modifier_type"] == Config.PRICE_MODIFIER_TYPE_DISCOUNT:
                real_discounted_price += float(modifier["discount_value"])
            if modifier["modifier_type"] == Config.PRICE_MODIFIER_TYPE_RULE:
                rule_value += float(modifier["discount_value"])

        not_worse_price_with_real_discounts_only = float(price["discounted_price"]) >= float(
            real_discounted_price
        )

        better_with_rules = rule_value != 0 and not_worse_price_with_real_discounts_only

        return discounted_price_better or same_price_with_fewer_discounts or better_with_rules

    @staticmethod
    def _merge_offers(original_offer, offer):
        if not original_offer["has_merged_free_nights"]:
            original_offer["original_price"] += offer["original_price"]
            if offer["has_merged_free_nights"]:
                original_offer["original_price"] = offer["original_price"]
                original_offer["has_merged_free_nights"] = offer["has_merged_free_nights"]

        original_offer["discounted_price"] += offer["discounted_price"]

        original_offer["period"]["date_from"] = min(
            datetimestr_to_datetime(original_offer["period"]["date_from"]),
            datetimestr_to_datetime(offer["period"]["date_from"]),
        )
        original_offer["period"]["date_to"] = max(
            datetimestr_to_datetime(original_offer["period"]["date_to"]),
            datetimestr_to_datetime(offer["period"]["date_to"]),
        )

        original_offer["discounts"] += offer["discounts"]

        # fixed price only once
        applied_fixed_price_ids = []
        applied_free_nights_price_ids = {}
        for index, discount in enumerate(reversed(original_offer["discounts"])):
            real_index = len(original_offer["discounts"]) - index - 1
            if discount["offer"] == "fixed_price":
                if discount["id"] in applied_fixed_price_ids:
                    original_offer["discounted_price"] -= discount["discount_value"]
                    del original_offer["discounts"][real_index]
                applied_fixed_price_ids.append(discount["id"])
            if discount["offer"] == "free_nights":
                if (
                    discount["id"] in applied_free_nights_price_ids.keys()
                    and discount["period"]["date_from"]
                    == applied_free_nights_price_ids[discount["id"]]["date_from"]
                    and discount["period"]["date_to"]
                    == applied_free_nights_price_ids[discount["id"]]["date_to"]
                ):
                    original_offer["discounted_price"] -= discount["discount_value"]
                    del original_offer["discounts"][real_index]
                applied_free_nights_price_ids[discount["id"]] = discount["period"]

        return original_offer

    def _calculate_prices(self, order_itemable_index, productable_id_field, productable_id):
        usage_prices = self._calculate_productable_prices(order_itemable_index, productable_id)
        
        if type(usage_prices) is not list and usage_prices["prices"] is None:
            return usage_prices

        best_productable_prices = self._best_productable_prices(
            usage_prices, productable_id_field, productable_id
        )
        best_productable_prices = self._apply_rules(best_productable_prices)
        return AbstractSearch._serialize_prices(
            best_productable_prices, self.price_search.remove_request, self.display_margin
        )

    def _apply_rules(self, best_productable_prices):
        if self.rule_wrappers:
            rule_calculator = RuleCalculator(
                plpy=self.plpy,
                subject_type=self.price_search.params["subject_type"],
                subject_id=self.price_search.params["subject_id"],
                productable_type=self.price_search.params["productable_type"],
                productable_id=self.price_search.params["productable_id"],
                price_modifiable_type=self.price_search.params["price_modifiable_type"],
                price_modifiable_id=self.price_search.params["price_modifiable_id"],
                order_itemable_index=self.price_search.order_itemable_index,
                rule_wrappers=self.rule_wrappers,
                settings=self.settings,
                meal_plans=self.meal_plans,
            )

            for prices in best_productable_prices["prices"]:
                prices = rule_calculator.get_offer(prices)
        return best_productable_prices

    @staticmethod
    def _serialize_prices(productable_prices, remove_request, display_margin):
        for prices in productable_prices["prices"]:
            if remove_request:
                del prices["room_request"]
            total_discount_value = 0
            for discount in prices["discounts"]:
                total_discount_value += discount["discount_value"]
                discount["discount_percentage"] = AbstractSearch._calculate_percentage(
                    discount["discount_value"], prices["original_price"]
                )
                if "id" in discount:
                    del discount["id"]
                if "period" in discount:
                    del discount["period"]
                    # discount['period'] = {key:str(val) for key,val in discount['period'].iteritems()}
                if "offer_summary" in discount:
                    del discount["offer_summary"]
            del prices["period"]  # TODO should we need them?
            del prices["has_merged_free_nights"]
            if "meal_offer" in prices:
                del prices["meal_offer"]
            if "discounted_meal_offer" in prices:
                del prices["discounted_meal_offer"]
            if "switches" in prices:
                del prices["switches"]

            prices["discounted_price"] = prices["original_price"] + total_discount_value
            prices = AbstractSearch._calculate_totals(prices)
            prices["discounted_price"] = str(
                prices["discounted_price"]
            )  # TODO should we need this conversion?
            prices["original_price"] = str(prices["original_price"])  # TODO should we need this conversion?
            if not display_margin:
                del prices["margin"]
        return productable_prices

    @staticmethod
    def _calculate_percentage(calculated_modification, original_price):
        if original_price is None or float(original_price) == 0:
            return None
        return round(calculated_modification / float(original_price) * 100, 2)

    @staticmethod
    def _calculate_totals(offer):
        total_modification = float(offer["discounted_price"]) - float(offer["original_price"])
        total_percentage = 0
        if float(offer["original_price"]) > 0:
            total_percentage = AbstractSearch._calculate_percentage(
                total_modification, float(offer["original_price"])
            )

        if offer["discounts"]:
            offer["total_discount"] = {"value": total_modification, "percentage": total_percentage}
            offer["margin"] = offer["margin"] * (1 + total_percentage / 100)

        return offer
