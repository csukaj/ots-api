from json import dumps

from ots.common.config import Config
from ots.common.date_range_cooker import DateRangeCooker
from ots.price_modifier.rule_wrapper import RuleWrapper
from ots.price_modifier.switch_wrapper import SwitchWrapper
from ots.pricing.price_modifier_range_cooker import PriceModifierRangeCooker
from ots.price_modifier.combination_wrapper import CombinationWrapper
from ots.search.abstract_search import AbstractSearch
from ots.usage_and_availability.room_matcher import RoomMatcher
from ots.pricing.room_price_search import RoomPriceSearch
from stylers.utils import clear_query_cache


class RoomSearch(AbstractSearch):
    def __init__(self, **keyword_parameters):
        super(RoomSearch, self).__init__(**keyword_parameters)

        clear_query_cache()
        self.price_search = RoomPriceSearch(
            plpy=self.plpy,
            from_time=self.from_time,
            to_time=self.to_time,
            booking_time=self.booking_time,
            wedding_time=self.wedding_time,
            cart_summary=self.cart_summary,
            settings=self.settings,
            abstract_search=self,
        )

        self._load_request_handler("App\\Organization", self.organization_id)
        self.room_matcher = RoomMatcher(
            plpy=self.plpy,
            request_handler=self.request_handler,
            organization_id=self.organization_id,
            cart_summary=self.cart_summary,
            show_inactive=self.show_inactive,
        )
        self.room_matcher.set_interval(self.from_date, self.to_date)
        self.available_devices = self.room_matcher.check()

    def get_rooms(self):
        """
        Class endpoint
        """

        # import ptvsd
        # ptvsd.enable_attach(address=('172.20.0.5', 12312), redirect_output=True)
        # ptvsd.wait_for_attach()

        # import pydevd_pycharm
        # pydevd_pycharm.settrace('172.20.0.1', port=12312, stdoutToServer=True, stderrToServer=True, suspend=False)

        if self.available_devices is None:
            return None

        if not self.is_interval_query:
            return dumps({"availability": self.available_devices, "results": None})

        self.date_ranges = self._get_open_and_price_modifier_date_ranges(
            "App\\Organization", self.organization_id
        )
        cooker = DateRangeCooker(
            plpy=self.plpy,
            date_rangeable_type="App\\Organization",
            date_rangeable_id=self.organization_id,
            from_time=self.from_time,
            to_time=self.to_time,
            date_ranges=self.date_ranges,
        )

        if not cooker.is_covered():
            return None

        if not self.room_matcher.has_common_meal_plan(
            self.date_ranges, "App\\Organization", self.organization_id
        ):
            return None

        self._load_meal_plans("App\\Organization", self.organization_id)

        price_modifier_range_cooker = PriceModifierRangeCooker(
            plpy=self.plpy,
            date_rangeable_type="App\\Organization",
            date_rangeable_id=self.organization_id,
            from_time=self.from_time,
            to_time=self.to_time,
            date_ranges=self.date_ranges,
        )
        price_modifier_range_cooker.add_annual_price_modifiers()
        price_modifier_range_cooker.merge_multi_period_price_modifier_ranges()

        self.price_modifiers = self._get_price_modifiers(
            "App\\Organization",
            self.organization_id,
            self.date_ranges,
            self.available_devices,
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
        )
        self.rule_wrappers = RuleWrapper.factory(
            self.plpy,
            self.settings,
            self.price_modifiers[Config.PRICE_MODIFIER_TYPE_RULE],
            self.from_time,
            self.to_time,
        )

        results = {}
        order_itemable_indexes = range(len(self.request_handler.request))
        for order_itemable_index in order_itemable_indexes:
            for device in self.available_devices:
                has_price = False
                if order_itemable_index in device["usage_pairs"]:
                    # check if it has price - and calculate prices with or without price modifier
                    device_prices = self._calculate_device_prices(order_itemable_index, device["device_id"])

                    if "prices" in device_prices and device_prices["prices"] is not None:
                        has_price = True
                        results.setdefault(order_itemable_index, []).append(device_prices)
                    if not has_price:
                        del device

        if not results or order_itemable_indexes != results.keys():
            return None

        return dumps({"availability": self.available_devices, "results": results})

    def _calculate_device_prices(self, order_itemable_index, productable_id):
        """
        Calculate prices for one device and one room request
        """
        return self._calculate_prices(order_itemable_index, "device_id", productable_id)
