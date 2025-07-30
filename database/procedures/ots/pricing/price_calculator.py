from ots.repository.model.price_row_collection_per_meal_plan_model import PriceRowCollectionPerMealPlanModel
from ots.common.config import Config
from ots.repository.model_meal_plan_repository import ModelMealPlanRepository
from ots.repository.price_repository import PriceRepository
from stylers.date_helpers import get_days, cover_nights
from stylers.utils import execute_cached_query
from ots.repository.utils.object_mapper import ObjectMapper
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
from ots.repository.exceptions.unable_to_map_data_error import UnableToMapDataError


class DateRangeTypes(dict):
    """
    The goal here is to create a wrapper class for enabling iteration over date_ranges
    even if they are not present. Using this class can improve readability.
    """

    def __init__(self, iterable):

        super(DateRangeTypes, self).__init__(
            {
                Config.DATE_RANGE_TYPE_CLOSED: iterable.get(Config.DATE_RANGE_TYPE_CLOSED, []),
                Config.DATE_RANGE_TYPE_OPEN: iterable.get(Config.DATE_RANGE_TYPE_OPEN, []),
                Config.DATE_RANGE_TYPE_PRICE_MODIFIER: iterable.get(
                    Config.DATE_RANGE_TYPE_PRICE_MODIFIER, []
                ),
            }
        )

        if self.get(Config.DATE_RANGE_TYPE_CLOSED, []) is None:
            self[Config.DATE_RANGE_TYPE_CLOSED] = []
        if self.get(Config.DATE_RANGE_TYPE_OPEN, []) is None:
            self[Config.DATE_RANGE_TYPE_OPEN] = []
        if self.get(Config.DATE_RANGE_TYPE_PRICE_MODIFIER, []) is None:
            self[Config.DATE_RANGE_TYPE_PRICE_MODIFIER] = []

    def filter(self, callable):
        return DateRangeTypes(
            {
                Config.DATE_RANGE_TYPE_CLOSED: filter(callable, self[Config.DATE_RANGE_TYPE_CLOSED]),
                Config.DATE_RANGE_TYPE_OPEN: filter(callable, self[Config.DATE_RANGE_TYPE_OPEN]),
                Config.DATE_RANGE_TYPE_PRICE_MODIFIER: filter(
                    callable, self[Config.DATE_RANGE_TYPE_PRICE_MODIFIER]
                ),
            }
        )


"""
currently it's also a price getter
"""


class PriceCalculator:
    def __init__(self, **keyword_parameters):
        self.from_time = keyword_parameters["from_time"]
        self.to_time = keyword_parameters["to_time"]
        self.productable_type = keyword_parameters["productable_type"]
        self.productable_id = keyword_parameters["productable_id"]
        self.price_modifiable_type = keyword_parameters["price_modifiable_type"]
        self.price_modifiable_id = keyword_parameters["price_modifiable_id"]
        self.age_resolver = keyword_parameters["age_resolver"]
        self.product_ids = keyword_parameters.get("product_ids")

        # TODO: the data should be the dependency instead of the repositories or plpy
        plpy = keyword_parameters["plpy"]
        self.price_repository = PriceRepository(plpy)
        self.model_meal_plan_repository = ModelMealPlanRepository(plpy)

        self.deduction_calculation_without_mandatory = keyword_parameters.get(
            "deduction_calculation", False)
        self.deduction_base_prices = keyword_parameters.get(
            "deduction_base_prices")
        if self.deduction_base_prices:
            self.deduction_base_prices = {
                k: v for k, v in self.deduction_base_prices.iteritems() if int(v) > 0
            }

        self.age_ranges = self.age_resolver.age_ranges

        self.prices = self._get_prices(
            self.productable_type in ["App\\Device", "App\\CruiseDevice"],
            self.productable_type,
            self.productable_id,
            self.product_ids,
        )

        """
        DateRanges and PriceElements
        """
        date_ranges = DateRangeTypes(keyword_parameters["date_ranges"])
        self.price_elements = self.price_repository.get_price_elements(
            date_ranges, self.prices)
        self.date_ranges = self._get_applicable_date_ranges(
            date_ranges=date_ranges,
            from_time=self.from_time,
            to_time=self.to_time,
            price_elements=self.price_elements,
            product_ids=self.product_ids,
        )

        self.common_meal_plans = self.model_meal_plan_repository.get_common_meal_plans_with_date_ranges(
            date_range_ids=self._get_date_range_ids(self.date_ranges)
        )

        self._counter = {}
        self.applied_date_ranges = []
        self.uncovered_nights = get_days(
            self.from_time, self.to_time, count_nights=True)

    class Offers(dict):
        def __init__(self, room_request, usage, offers):
            super(PriceCalculator.Offers, self).__init__(
                room_request = room_request,
                usage=usage,
                offers=offers
            )
            self.room_request = room_request
            self.usage = usage
            self.offers = offers  # type: PriceRowCollectionPerMealPlanModel

    def get_offers(self, room_request):
        # type: (...) -> PriceCalculator.Offers or None
        # room_request: {"usage": [{"age": 21, "amount": 2}]}

        price_amounts = PriceRowCollectionPerMealPlanModel(
            price_row_collection_per_meal_plan={}
        )  # type : PriceRowCollectionPerMealPlanModel

        usage = self.age_resolver.resolve_room_usage(
            room_request["usage"], True)
        # usage: {"adult": 2}

        for date_range in self.date_ranges:
            if type(date_range["id"]) is int:
                date_range_price_amounts = self._get_price_amounts_for_date_range(
                    self.prices,
                    usage,
                    date_range,
                    self.productable_type,
                    self.applied_date_ranges,
                    self.price_elements,
                    self.common_meal_plans,
                    self.uncovered_nights,
                    self.deduction_base_prices,
                    self.product_ids,
                )
                if date_range_price_amounts is not None:
                    price_amounts = price_amounts + date_range_price_amounts
            else:
                for date_range_id in date_range["id"]:
                    range_by_id = self._get_date_range_by_id(date_range_id)
                    if not range_by_id:
                        continue
                    date_range_price_amounts = self._get_price_amounts_for_date_range(
                        self.prices,
                        usage,
                        range_by_id,
                        self.productable_type,
                        self.applied_date_ranges,
                        self.price_elements,
                        self.common_meal_plans,
                        self.uncovered_nights,
                        self.deduction_base_prices,
                        self.product_ids,
                    )
                    if date_range_price_amounts is not None:
                        price_amounts = price_amounts + date_range_price_amounts

        # filter out meal plans that not available in all applied date ranges
        # WARNING: MUTATES THE price_amounts
        for meal_plan_id in price_amounts.get("price_row_collection_per_meal_plan"):
            if self._counter.get(meal_plan_id, 999) < len(self.applied_date_ranges):
                price_amounts.get(
                    "price_row_collection_per_meal_plan").pop(meal_plan_id)

        return PriceCalculator.Offers(room_request=room_request, usage=usage, offers=price_amounts)

    """
    private:
    """

    # clean
    def _get_price_amounts_for_date_range(
        self,
        prices,
        usage,
        date_range,
        productable_type,
        applied_date_ranges,
        price_elements,
        common_meal_plans,
        uncovered_nights,
        deduction_base_prices,
        product_ids=None,
    ):
        # type: () -> Optional[PriceRowCollectionPerMealPlanModel]

        date_range_price_amounts = None
        if productable_type in ["App\\Device", "App\\CruiseDevice"]:
            if product_ids is not None:
                date_range_price_amounts = self._get_device_price_amounts(
                    prices, usage, date_range, deduction_base_prices, product_ids
                )
            if date_range_price_amounts is None:
                date_range_price_amounts = self._get_device_price_amounts(
                    prices, usage, date_range, deduction_base_prices
                )
        elif productable_type == "App\\ShipGroup":
            date_range_price_amounts = self._get_organization_group_price_amounts(
                prices)
        else:
            raise ValueError(
                "Not supported productable type: " + productable_type)
        # date_range_price_amounts: [{"price": {...}, "amount": 1, "net_offer": {}, "rack_offer": {}}]

        if date_range_price_amounts is None:
            return None

        date_range_price_amounts = self._calculate_price_offers(
            date_range_price_amounts,
            date_range,
            applied_date_ranges=applied_date_ranges,
            price_elements=price_elements,
            common_meal_plans=common_meal_plans,
            uncovered_nights=uncovered_nights,
        )
        # date_range_price_amounts: [{"price": {...}, "amount": 1, "net_offer": {2: 12, 3: 32}, "rack_offer": {2: 23, 3: 43}}]

        price_amounts_per_meal_plan = self.transpose_price_amounts_on_meal_plans(
            date_range_price_amounts)

        # this will give a result like: {
        #   2: [{"meta": {}, "amount": 1, "net": 12, "rack": 23}],
        #   3: [{"meta": {}, "amount": 1, "net": 32, "rack": 43}],
        # }

        for meal_plan_id, price_amounts in price_amounts_per_meal_plan.iteritems():
            price_amounts_per_meal_plan[meal_plan_id] = {
                'price_rows': {
                    price_amount["meta"]["price_name"]: price_amount for price_amount in price_amounts
                }
            }

        price_row_collection_per_meal_plan_model = ObjectMapper().map(
            {"price_row_collection_per_meal_plan": price_amounts_per_meal_plan},
            PriceRowCollectionPerMealPlanModel,
        )
        # type: PriceRowCollectionPerMealPlanModel or None
        if price_row_collection_per_meal_plan_model is None:
            raise UnableToMapDataError("")
        else:
            return price_row_collection_per_meal_plan_model

    def transpose_price_amounts_on_meal_plans(self, date_range_price_amounts):
        # type: (list) -> dict
        transposed = {}

        for price_amount in date_range_price_amounts:
            for meal_plan_id, _offer in price_amount["net_offer"].iteritems():
                transposed.setdefault(meal_plan_id, [])
                transposed[meal_plan_id].append(
                    {
                        "meta": price_amount["price"],
                        "amount": price_amount["amount"],
                        "net": price_amount["net_offer"][meal_plan_id],
                        "rack": price_amount["rack_offer"][meal_plan_id],
                    }
                )
        return transposed

    # clean
    def _get_organization_group_price_amounts(self, prices):
        if prices[Config.PRODUCT_TYPE_ACCOMMODATION_TX_ID]:
            return [
                {
                    "price": prices[Config.PRODUCT_TYPE_ACCOMMODATION_TX_ID][0],
                    "amount": 1,
                    "net_offer": {},
                    "rack_offer": {},
                }
            ]
        else:
            return None

    # clean
    def _get_device_price_amounts(self, prices, usage, date_range, deduction_base_prices, product_ids=None):

        if self.deduction_calculation_without_mandatory:
            mandatory_price = self._get_base_price_for_deduction(
                prices, usage, date_range, product_ids, deduction_base_prices
            )
        else:
            mandatory_price = self._get_mandatory_price(
                prices, date_range, product_ids)

            if mandatory_price is None:
                return None

        base_headcount = 0

        extra_headcounts = {"adult": 0}
        extra_headcounts.update(
            {age_range["name"]: 0 for age_range in self.age_ranges})

        # step 1: split usage into headcounts
        # There are two types of headcounts:
        # Base and extra.
        # Base headcount will be determined using the mandatory_price['amount']
        #
        descending_age_ranges = reversed(self.age_ranges)
        for age_range in descending_age_ranges:
            if age_range["name"] in usage:
                for _ in range(0, usage[age_range["name"]]):
                    if base_headcount < mandatory_price["amount"]:
                        base_headcount += 1
                    else:
                        extra_headcounts[age_range["name"]] += 1

        base_prices = self._get_base_prices(
            prices, date_range, product_ids, deduction_base_prices)

        # step 2: find fitting prices

        # Theorem: If there is no extra usage,
        # the base headcounts may fit well a smaller non-mandatory price
        if sum(extra_headcounts.values()) == 0:
            # base price only
            if base_headcount < mandatory_price["amount"]:
                for price in base_prices:
                    if base_headcount <= price["amount"]:
                        return [{"price": price, "amount": 1, "net_offer": {}, "rack_offer": {}}]
            return [{"price": mandatory_price, "amount": 1, "net_offer": {}, "rack_offer": {}}]

        # Theorem: If there are "adult" extra usages,
        # may we can find a better fitting price with bigger amount of usage
        base_price = mandatory_price
        if extra_headcounts["adult"] > 0:
            for price in base_prices:
                if base_headcount < price["amount"] <= (base_headcount + extra_headcounts["adult"]):
                    base_headcount = price["amount"]
                    extra_headcounts["adult"] -= price["amount"] - \
                        base_price["amount"]
                    base_price = price

        # Theorem: If an age range free and there is no extra price with that age range, we should return
        # {"price": None, "amount": headcount in this range, "net_offer": {}, "rack_offer": {}}
        # Theorem: If the age range is not free and we can find extra price with that age range, we should
        # return {"price": extra_price, "amount": headcount in this range, "net_offer": {}, "rack_offer": {}}
        extra_prices = []
        for age_range in self.age_ranges:
            if extra_headcounts[age_range["name"]] > 0:
                there_is_an_extra_price_with_this_age_range = self._is_there_any_extra_price_with_this_age_range(
                    prices, age_range["name"], date_range, product_ids
                )

                if age_range["free"] and not there_is_an_extra_price_with_this_age_range:
                    extra_prices.append(
                        {
                            "price": None,
                            "amount": extra_headcounts[age_range["name"]],
                            "net_offer": {},
                            "rack_offer": {},
                        }
                    )
                    extra_headcounts[age_range["name"]] = 0
                else:
                    extra_prices = self._find_price_for_extras(
                        prices, age_range["name"], extra_headcounts, extra_prices, date_range, product_ids
                    )

        # step 3: try to cover remaining pax with bigger base prices
        remaining_headcount = sum(extra_headcounts.values())
        if remaining_headcount > 0:
            if not deduction_base_prices:
                for price in base_prices:
                    if base_headcount + remaining_headcount <= price["amount"]:
                        base_headcount += remaining_headcount
                        extra_headcounts = {
                            age_range: 0 for age_range in extra_headcounts.keys()}
                        base_price = price
                        break
            else:
                for price in sorted(base_prices, key=lambda p: p["amount"], reverse=True):
                    if remaining_headcount >= price["amount"] > base_price["amount"]:
                        base_headcount += price["amount"]
                        extra_headcounts = {
                            age_range: 0 for age_range in extra_headcounts.keys()}
                        base_price = price
                        break

        if deduction_base_prices:
            extra_prices = self._get_restricted_prices_for_deduction(
                price_amounts=extra_prices, deduction_base_prices=deduction_base_prices
            )

        # results
        if sum(extra_headcounts.values()) > 0 and not deduction_base_prices:
            return None
        return [{"price": base_price, "amount": 1, "net_offer": {}, "rack_offer": {}}] + extra_prices

    # clean
    def _is_there_any_extra_price_with_this_age_range(self, prices, age_range, date_range, product_ids=None):
        available_extra_prices = self._get_extra_prices(
            prices, date_range, product_ids)
        for extra_price in available_extra_prices:
            if extra_price["age_range"] == age_range:
                return True
        return False

    # clean
    def _find_price_for_extras(
        self, prices, age_range, extra_headcounts, extra_prices, date_range, product_ids=None
    ):
        if extra_headcounts[age_range] == 0:
            return extra_prices

        # find extra price only for the searched age range, not in bigger age ranges
        available_extra_prices = self._get_extra_prices(
            prices, date_range, product_ids)

        for extra_price in available_extra_prices:
            if extra_price["age_range"] == age_range:
                amount_to_subtract = extra_headcounts[age_range]
                extra_prices.append(
                    {"price": extra_price, "amount": amount_to_subtract,
                        "net_offer": {}, "rack_offer": {}}
                )
                extra_headcounts[age_range] -= amount_to_subtract
                return extra_prices

        return extra_prices

    # clean
    def _get_base_prices(self, prices, date_range, product_ids=None, deduction_base_prices=None):
        """
        returns non-extra prices from self.prices which is not empty and satisfies the given date_range and product_ids

        Future refactor notes:
        Magic:
        If the calculation is for determining the the deduction base price, the result will be filtered by _filter_prices_for_deduction (whatever you want).
        this condition should be placed upwards
        """

        if product_ids is not None:
            price_list = prices[Config.PRODUCT_TYPE_PRICE_MODIFIED_ACCOMMODATION_TX_ID]
        else:
            price_list = prices[Config.PRODUCT_TYPE_ACCOMMODATION_TX_ID]

        def _is_price_for_deduction(price):
            return price["price_name"] in deduction_base_prices.keys()

        base_prices = [
            price
            for price in price_list
            if not price["extra"] and date_range["id"] in price["non_empty_date_ranges"]
            if not deduction_base_prices or _is_price_for_deduction(price)
        ]

        return base_prices

    # clean
    def _get_extra_prices(self, prices, date_range, product_ids=None):
        """
        returns extra prices from self.prices which is not empty and satisfies the given date_range and product_ids
        """

        if product_ids is not None:
            price_list = prices[Config.PRODUCT_TYPE_PRICE_MODIFIED_ACCOMMODATION_TX_ID]
        else:
            price_list = prices[Config.PRODUCT_TYPE_ACCOMMODATION_TX_ID]

        return [
            price
            for price in price_list
            if price["extra"] and date_range["id"] in price["non_empty_date_ranges"]
        ]

    # clean
    def _get_mandatory_price(self, prices, date_range, product_ids=None):
        """
        :returns Price the mandatory price from prices based on the given filter
        :returns None if the given filter is empty range
        """

        if product_ids:
            for price in prices[Config.PRODUCT_TYPE_PRICE_MODIFIED_ACCOMMODATION_TX_ID]:
                if (
                    price["mandatory"]
                    and str(price["product_id"]) in product_ids
                    and date_range["id"] in price["non_empty_date_ranges"]
                ):
                    return price

        for price in prices[Config.PRODUCT_TYPE_ACCOMMODATION_TX_ID]:
            if price["mandatory"] and date_range["id"] in price["non_empty_date_ranges"]:
                return price
        return None

    # clean
    def _get_base_price_for_deduction(
        self, prices, usage, date_range, product_ids=None, deduction_base_prices=None
    ):
        """
        :returns Price the first non-extra price for deduction. Based on the MAGIC of the _get_base_prices.
        :returns None if it cannot find fitting price
        """

        if "adult" not in usage:
            return {"age_range": "adult", "amount": 0}

        for price in self._get_base_prices(prices, date_range, product_ids, deduction_base_prices):
            if usage["adult"] <= price["amount"]:
                return price

        return {"age_range": "adult", "amount": 0}

    # clean
    def _get_prices(self, with_age_ranges, productable_type, productable_id, product_ids):

        prices = self.price_repository.get_prices(
            with_age_ranges, product_ids, productable_type, productable_id
        )

        result_prices = {
            Config.PRODUCT_TYPE_ACCOMMODATION_TX_ID: [],
            Config.PRODUCT_TYPE_PRICE_MODIFIED_ACCOMMODATION_TX_ID: [],
        }

        for price in prices:
            if price["non_empty_date_ranges"]:
                result_prices[price["product_type_taxonomy_id"]].append(price)

        return result_prices

    # this should be part of an DateRangeCollection
    def _get_date_range_ids(self, date_ranges):
        """
        returns the available ids from self.date_ranges
        returns empty list if there is no self.date_ranges

        Future refactor notes:
        Unfortunately it can be both list or int instead of only list (which would be logical).
        So getting the ids is tricky.
        """
        if not date_ranges:
            return []
        ids = []
        for date_range in date_ranges:
            if "id" in date_range:
                if type(date_range["id"]) is int:
                    ids.append(str(date_range["id"]))
                else:
                    for real_id in date_range["id"]:
                        ids.append(str(real_id))

        return ids

    # this should be part of an DateRangeCollection
    def _get_date_range_by_id(self, date_range_id):
        """
        date_range['id'] is can be both int or list<int>
        But date_range['id'] is an identifier. How can it be an array?

        """
        if not self.date_ranges:
            return None
        for date_range in self.date_ranges:
            if "id" in date_range and type(date_range["id"]) is int and date_range_id == date_range["id"]:
                return date_range
        return None

    # clean except _counter and applied_date_ranges
    def _calculate_price_offers(
        self,
        price_amounts,
        date_range,
        applied_date_ranges,
        price_elements,
        common_meal_plans,
        uncovered_nights,
    ):
        """
        :param price_amounts is a list of price objects
        returns price_amounts if there is no coverable nights with the given date_range
        returns None if there is no self.uncovered_nights and price_elements
        """

        nights = cover_nights(uncovered_nights, date_range)
        if nights == 0:
            return price_amounts
        else:
            applied_date_ranges.append(date_range["id"])

        price_ids = [
            price_amount["price"]["id"]
            for price_amount in price_amounts
            if price_amount["price"] and "id" in price_amount["price"]
        ]

        price_elements = self._filter_price_elements_for_date_range_and_price_ids(
            price_ids, date_range["id"], price_elements=price_elements, common_meal_plans=common_meal_plans
        )

        if uncovered_nights and not price_elements:
            return None

        for price_amount in price_amounts:
            if price_amount["price"] and "id" in price_amount["price"]:
                price_elements_for_price = (
                    price_element
                    for price_element in price_elements
                    if price_element["price_id"] == price_amount["price"]["id"]
                )

                for price_element in price_elements_for_price:
                    meal_plan_id = price_element["meal_plan_id"]
                    if meal_plan_id not in price_amount["net_offer"]:
                        price_amount["net_offer"][meal_plan_id] = 0
                        price_amount["rack_offer"][meal_plan_id] = 0

                    price_amount["net_offer"][meal_plan_id] += nights * \
                        price_element["net_price"]
                    price_amount["rack_offer"][meal_plan_id] += nights * \
                        price_element["rack_price"]

                    self._counter[meal_plan_id] = self._counter.get(
                        meal_plan_id, 0) + 1

        return price_amounts

    # prices + deduction_base_prices
    def _get_restricted_prices_for_deduction(self, price_amounts, deduction_base_prices):
        # price_amounts e.g: [{price:{price_name:'...'}, amount:2, ...}, ...]
        # deduction_base_prices e.g: {'Child 0-3': 2}
        # where 2 is the maximum amount

        return [
            {
                "price": price["price"],
                "amount": min(price["amount"], deduction_base_prices.get(price["price"]["price_name"], 999)),
                "net_offer": price["net_offer"],
                "rack_offer": price["rack_offer"],
            }
            for price in price_amounts
            if price["price"]["price_name"] in deduction_base_prices.keys()
        ]

    ###
    # for self.date_ranges :
    ###

    # it's not exactly for self.date_ranges but date_range + price_elements

    def _filter_price_elements_for_date_range_and_price_ids(
        self, price_ids, date_range_id, price_elements, common_meal_plans
    ):

        return [
            price_element
            for price_element in price_elements.get(date_range_id, [])
            if price_element["price_id"] in price_ids
            if int(price_element["meal_plan_id"]) in common_meal_plans
        ]

    # date_range + price_elements and I think it's return value should be dependency
    def _get_applicable_date_ranges(self, date_ranges, from_time, to_time, price_elements, product_ids):
        """
        unfortunately this can not be refactored easily due to the cover_days mutating behaviour
        """
        uncovered_nights = get_days(from_time, to_time, count_nights=True)

        uncovered_nights = get_days(
            self.from_time, self.to_time, count_nights=True)

        # an overcomplicated intersection
        # between date_ranges[Config.DATE_RANGE_TYPE_OPEN] and self.from_time, self.to_time
        if not self.product_ids:
            return list(
                filter(
                    lambda x: self._has_date_range_any_price(x, price_elements)
                    and cover_nights(uncovered_nights, x) > 0,
                    date_ranges[Config.DATE_RANGE_TYPE_OPEN],
                )
            )

        # an overcomplicated intersection
        # between date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER] and self.from_time, self.to_time
        applicable_ranges = list(
            filter(
                lambda x: self._has_date_range_any_price(x, price_elements)
                and cover_nights(uncovered_nights, x) > 0,
                date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER],
            )
        )

        if not len(applicable_ranges):
            return []

        for date_range in date_ranges[Config.DATE_RANGE_TYPE_OPEN]:
            if cover_nights(uncovered_nights, date_range) > 0:
                applicable_ranges.append(date_range)

        return applicable_ranges

    # date_range + price_elements
    def _has_date_range_any_price(self, date_range, price_elements):
        if "id" in date_range:
            date_range_ids = [date_range["id"]] if type(
                date_range["id"]) is int else date_range["id"]

            all_has_price = True
            for real_id in date_range_ids:
                all_has_price = all_has_price and self._is_date_range_in_price_elements(
                    real_id, price_elements
                )

            return all_has_price
        return False

    #  date_range + price_elements
    def _is_date_range_in_price_elements(self, date_range_id, price_elements):
        return date_range_id in price_elements and price_elements[date_range_id]
