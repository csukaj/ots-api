from copy import deepcopy
from datetime import timedelta
from json import loads

from ots.common.config import Config
from ots.price_modifier.price_modifier import PriceModifier
from ots.pricing.price_calculator import PriceCalculator
from ots.pricing.switch_calculator import SwitchCalculator
from stylers.date_helpers import datetimestr_to_datetime
from typing import Union


class Offer(object):
    def __init__(self, **keyword_parameters):
        self.plpy = keyword_parameters["plpy"]

        from_time = keyword_parameters["from_time"]
        combination_from_time = keyword_parameters.get(
            "combination_from_time") or from_time
        self.from_time = max(from_time, combination_from_time)

        to_time = keyword_parameters["to_time"]
        combination_to_time = keyword_parameters.get(
            "combination_to_time") or to_time
        self.to_time = min(to_time, combination_to_time)

        self.date_ranges = keyword_parameters["date_ranges"]
        self.price_modifiable_type = keyword_parameters["price_modifiable_type"]
        self.price_modifiable_id = keyword_parameters["price_modifiable_id"]
        self.price_modifier = keyword_parameters["price_modifier"]
        self.abstract_search = keyword_parameters["abstract_search"]
        self.age_resolver = keyword_parameters["age_resolver"]
        self.switch_calculation = True
        self.room_offer = None
        self.productable_type = None
        self.productable_id = None
        self.subject_type = None
        self.subject_id = None
        self.classification = self.get_classification()
        self.meta = self.get_meta()

    def get_classification(self):
        cls_data = PriceModifier.deserialize_properties(
            self.price_modifier.properties["offer_classifications"]
        )
        return [self.abstract_search.taxonomies[str(row["value_taxonomy_id"])] for row in cls_data]

    def get_meta(self):
        meta = {}
        meta_data = PriceModifier.deserialize_properties(
            self.price_modifier.properties["offer_metas"])
        for row in meta_data:
            name = self.abstract_search.taxonomies[str(row["taxonomy_id"])]
            meta[name] = row["value"]
        return meta

    def get_nights(self):
        min_max_delta = self.get_min_to() - self.get_max_from()
        return float(min_max_delta.days)

    def get_validity_nights(self):
        min_max_delta = self.price_modifier.valid_to - self.price_modifier.valid_from
        return float(min_max_delta.days)

    def get_max_from(self):
        return self.from_time  # max calculation  done at price_modifier

    def get_min_to(self):
        return self.to_time  # min calculation  done at price_modifier

    def calculate(
        self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
    ):
        # type: (...) -> OfferSummary or None
        self.room_offer = deepcopy(room_offer)
        self.productable_type = productable_type
        self.productable_id = productable_id
        self.subject_type = subject_type
        self.subject_id = subject_id
        self.switch_calculation = switch_calculation

    def get_minimum_price(self, meal_plan_id, deduction_base_prices, product_ids, allow_all_ranges):
        applicable_date_ranges = {
            Config.DATE_RANGE_TYPE_OPEN: self._get_applicable_date_ranges(
                self.date_ranges[Config.DATE_RANGE_TYPE_OPEN], allow_all_ranges
            ),
            Config.DATE_RANGE_TYPE_PRICE_MODIFIER: self._get_applicable_date_ranges(
                self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER], allow_all_ranges
            ),
        }

        max_from = self.get_max_from()
        min_to = self.get_min_to()
        if max_from.date() == min_to.date():
            min_to += timedelta(days=1)
        prices = self._get_price_of_date_range_and_meal_plan(
            applicable_date_ranges, meal_plan_id, deduction_base_prices, max_from, min_to, product_ids
        )

        return prices

    def _get_applicable_date_ranges(self, date_ranges, allow_all_ranges):

        if allow_all_ranges:
            return date_ranges

        applicable_date_ranges = []
        for date_range in date_ranges:

            if self._is_applicable_date_range(date_range):
                applicable_date_ranges.append(date_range)

        return applicable_date_ranges

    def _is_applicable_date_range(self, date_range):
        is_date_range_started_during_the_offer = (
            self.from_time <= datetimestr_to_datetime(
                date_range["from_time"]) <= self.to_time
        )
        is_offer_started_in_the_date_range = (
            datetimestr_to_datetime(date_range["from_time"])
            <= self.from_time
            <= datetimestr_to_datetime(date_range["to_time"])
        )
        return is_date_range_started_during_the_offer or is_offer_started_in_the_date_range

    def _get_price_of_date_range_and_meal_plan(
        self, applicable_date_ranges, meal_plan_id, deduction_base_prices, from_time, to_time, product_ids
    ):
        # type : (...) -> Optional[PriceRowCollectionModel]
        meal_plan_id = int(meal_plan_id)

        deduction_calculation = False

        if deduction_base_prices:
            deduction_base_prices = (
                loads(deduction_base_prices)
                if type(deduction_base_prices) is not dict
                else deduction_base_prices
            )

        if not deduction_base_prices:  # if empty string or None
            deduction_base_prices = None
        else:
            deduction_calculation = "use_mandatory_logic_for_deduction_base_prices" not in self.classification

        calculator = PriceCalculator(
            plpy=self.plpy,
            from_time=from_time,
            to_time=to_time,
            productable_type=self.productable_type,
            productable_id=self.productable_id,
            price_modifiable_type=self.price_modifiable_type,
            price_modifiable_id=self.price_modifiable_id,
            date_ranges=applicable_date_ranges,
            deduction_calculation=deduction_calculation,
            product_ids=product_ids,
            deduction_base_prices=deduction_base_prices,
            age_resolver=self.abstract_search.request_handler.age_resolver,
        )
        offers = calculator.get_offers(self.room_offer["room_request"])

        if offers is None or meal_plan_id not in offers.offers.get('price_row_collection_per_meal_plan'):
            return None

        modified_offers = deepcopy(offers)

        if self.switch_calculation:
            switch_calculator = SwitchCalculator(
                from_time=(datetimestr_to_datetime(from_time)
                           if from_time is not None else None),
                to_time=(datetimestr_to_datetime(to_time)
                         if to_time is not None else None),
                subject_type=self.subject_type,
                subject_id=self.subject_id,
                productable_type=self.productable_type,
                productable_id=self.productable_id,
                price_modifiable_type=self.price_modifiable_type,
                price_modifiable_id=self.price_modifiable_id,
                order_itemable_index=self.abstract_search.price_search.order_itemable_index,
                combination_wrapper=None,
                switch_wrappers=(
                    [
                        wrapper
                        for wrapper in self.abstract_search.price_search.switch_wrapper
                        if wrapper is not None
                    ]
                    if type(self.abstract_search.price_search.switch_wrapper) is list
                    else []
                ),
                settings=self.abstract_search.price_search.settings,
                meal_plans=self.abstract_search.meal_plans,
                deduction_base_prices=deduction_base_prices,
            )

            if modified_offers and meal_plan_id in modified_offers.offers.get('price_row_collection_per_meal_plan'):
                for offer_meal_plan_id, meal_offer in offers.offers.get('price_row_collection_per_meal_plan').iteritems():
                    modified_offers.offers.get('price_row_collection_per_meal_plan')[offer_meal_plan_id] = switch_calculator.get_offer(
                        offer_meal_plan_id, meal_offer, offers.room_request
                    )


        if modified_offers and meal_plan_id in modified_offers.offers.get('price_row_collection_per_meal_plan'):
            return modified_offers.offers.get('price_row_collection_per_meal_plan')[meal_plan_id]
        else:
            return None

    def get_calculations_base_price(self, prices):
        # type: (dict) -> float
        if self.abstract_search.settings["discount_calculations_base"] == "rack prices":
            return prices.rack
        else:
            return prices.net

    def is_meta_empty(self, meta_key):
        return self.meta.get(meta_key, "").strip() == ""

    def get_deduction_base_prices(self):
        deduction_base_prices_of_switch = self.price_modifier.deduction_base_prices_of_switch
        deduction_base_prices = self.meta.get("deduction_base_prices")
        if deduction_base_prices:
            deduction_base_prices = loads(deduction_base_prices)

        if not deduction_base_prices_of_switch:
            return deduction_base_prices
        elif not deduction_base_prices:
            return deduction_base_prices_of_switch
        else:
            common_deduction_base_prices = {}
            for price_name, amount in deduction_base_prices_of_switch.iteritems():
                if deduction_base_prices.get(price_name):
                    common_deduction_base_prices[price_name] = min(
                        amount, deduction_base_prices[price_name])
            return common_deduction_base_prices
