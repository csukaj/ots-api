from json import dumps

from ots.common.config import Config
from ots.common.date_range_cooker import DateRangeCooker
from ots.price_modifier.combination_wrapper import CombinationWrapper
from ots.price_modifier.rule_wrapper import RuleWrapper
from ots.price_modifier.switch_wrapper import SwitchWrapper
from ots.pricing.charter_price_search import CharterPriceSearch
from ots.pricing.price_modifier_range_cooker import PriceModifierRangeCooker
from ots.search.abstract_search import AbstractSearch
from ots.usage_and_availability.charter_matcher import CharterMatcher


class CharterSearch(AbstractSearch):
    def __init__(self, **keyword_parameters):
        super(CharterSearch, self).__init__(**keyword_parameters)

        self.organization_group_id = keyword_parameters.get("organization_group_id")

        self.price_search = CharterPriceSearch(
            plpy=self.plpy,
            from_time=self.from_time,
            to_time=self.to_time,
            booking_time=self.booking_time,
            wedding_time=self.wedding_time,
            cart_summary=self.cart_summary,
            settings=self.settings,
            abstract_search=self,
        )

        self._load_request_handler("App\\ShipGroup", self.organization_group_id)
        self.charter_matcher = CharterMatcher(
            plpy=self.plpy,
            request_handler=self.request_handler,
            organization_group_id=self.organization_group_id,
            cart_summary=self.cart_summary,
            show_inactive=self.show_inactive,
        )
        self.charter_matcher.set_interval(self.from_date, self.to_date)
        self.ship_group = self.charter_matcher.check()
        self.price_modifiers = None

    def get_charters(self):
        """
        Class endpoint
        """
        if not self.ship_group:
            return None

        if not self.is_interval_query:
            return dumps({"availability": self.ship_group, "results": None})

        self.date_ranges = self._get_open_and_price_modifier_date_ranges(
            "App\\ShipGroup", self.organization_group_id
        )
        cooker = DateRangeCooker(
            plpy=self.plpy,
            date_rangeable_type="App\\ShipGroup",
            date_rangeable_id=self.organization_group_id,
            from_time=self.from_time,
            to_time=self.to_time,
            date_ranges=self.date_ranges,
        )

        if not cooker.is_covered():
            return None

        if not self.charter_matcher.has_common_meal_plan(
            self.date_ranges, "App\\ShipGroup", self.organization_group_id
        ):
            return None

        self._load_meal_plans("App\\ShipGroup", self.organization_group_id)

        price_modifier_range_cooker = PriceModifierRangeCooker(
            plpy=self.plpy,
            date_rangeable_type="App\\ShipGroup",
            date_rangeable_id=self.organization_group_id,
            from_time=self.from_time,
            to_time=self.to_time,
            date_ranges=self.date_ranges,
        )
        price_modifier_range_cooker.add_annual_price_modifiers()
        price_modifier_range_cooker.merge_multi_period_price_modifier_ranges()

        self.price_modifiers = self._get_price_modifiers(
            "App\\ShipGroup",
            self.organization_group_id,
            self.date_ranges,
            [],
            self.request_handler.age_resolver,
        )
        self.switch_wrappers = SwitchWrapper.factory(
            self.plpy,
            self.settings,
            self.price_modifiers[Config.PRICE_MODIFIER_TYPE_SWITCH],
            self.from_time,
            self.to_time,
        )
        self.combination_wrappers = CombinationWrapper.factory(
            self.plpy,
            self.settings,
            self.price_modifiers[Config.PRICE_MODIFIER_TYPE_DISCOUNT],
            self.from_time,
            self.to_time,
            "App\\ShipGroup",
            self.organization_group_id,
        )
        self.rule_wrappers = RuleWrapper.factory(
            self.plpy,
            self.settings,
            self.price_modifiers[Config.PRICE_MODIFIER_TYPE_RULE],
            self.from_time,
            self.to_time,
        )

        # check if it has price - and calculate prices with or without price modifier
        charter_prices = self._calculate_charter_prices(0, self.organization_group_id)

        results = []
        if charter_prices.get("prices"):
            results.append(self._calculate_unit_prices(charter_prices))

        return dumps({"availability": self.ship_group, "results": results})

    def _calculate_charter_prices(self, order_itemable_index, productable_id):
        """
        Calculate prices for one device and one room request
        """
        charter_prices = self._calculate_prices(order_itemable_index, "ship_group_id", productable_id)

        if type(charter_prices) is not list and charter_prices["prices"] is None:
            charter_prices["ship_group_id"] = productable_id

        return charter_prices

    def _calculate_unit_prices(self, charter_prices):
        if self.ship_group.get("required_ship_count", 0) < 2:
            return charter_prices
        for price in charter_prices["prices"]:
            price["discounted_unit_price"] = price["discounted_price"]
            price["discounted_price"] = str(
                float(price["discounted_price"]) * self.ship_group["required_ship_count"]
            )  # TODO should we need this conversion?
            price["original_unit_price"] = price["original_price"]
            price["original_price"] = str(
                float(price["original_price"]) * self.ship_group["required_ship_count"]
            )  # TODO should we need this conversion?
            price["required_units"] = self.ship_group["required_ship_count"]
        return charter_prices
