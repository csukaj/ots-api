from copy import copy
from datetime import datetime, timedelta
from json import dumps

from ots.common.config import Config
from ots.common.date_range_cooker import DateRangeCooker
from ots.price_modifier.combination_wrapper import CombinationWrapper
from ots.price_modifier.rule_wrapper import RuleWrapper
from ots.price_modifier.switch_wrapper import SwitchWrapper
from ots.pricing.cruise_price_search import CruisePriceSearch
from ots.pricing.price_modifier_range_cooker import PriceModifierRangeCooker
from ots.search.abstract_search import AbstractSearch
from ots.usage_and_availability.cabin_matcher import CabinMatcher
from ots.usage_and_availability.cruise_matcher import CruiseMatcher


class CruiseSearch(AbstractSearch):
    def __init__(self, **keyword_parameters):
        super(CruiseSearch, self).__init__(**keyword_parameters)
        self.cruise_id = keyword_parameters.get("cruise_id")
        self._load_request_handler("App\\Cruise", self.cruise_id)

        self.cruise_matcher = CruiseMatcher(
            plpy=self.plpy,
            request_handler=self.request_handler,
            organization_id=self.organization_id,
            cruise_id=self.cruise_id,
            cart_summary=self.cart_summary,
            show_inactive=self.show_inactive,
        )

        self.cabin_matcher = CabinMatcher(
            plpy=self.plpy,
            request_handler=self.request_handler,
            organization_id=self.organization_id,
            cruise_id=self.cruise_id,
            cart_summary=self.cart_summary,
            show_inactive=self.show_inactive,
        )

        self.price_search = None

    def get_cabins(self):
        """
        Class endpoint
        """
        self.cruise_matcher.set_interval(self.from_date, self.to_date)
        schedules = self.cruise_matcher.check()
        if not schedules:
            return []

        if not self.is_interval_query:
            result = self._get_cabins_for_interval(None, None)
            return dumps([result] if result is not None else [])

        results = []
        for schedule in schedules:
            for from_date in schedule["dates"]:
                financial_from_date = datetime.strptime(from_date, "%Y-%m-%d") + timedelta(
                    days=schedule["financial_offset_days"]
                )
                financial_to_date = financial_from_date + timedelta(days=schedule["financial_length_days"])
                result = self._get_cabins_for_interval(
                    str(financial_from_date)[:10], str(financial_to_date)[:10]
                )

                if result is not None:
                    result["schedule"] = copy(schedule)
                    result["schedule"]["from_date"] = from_date
                    result["schedule"]["to_date"] = str(
                        datetime.strptime(from_date, "%Y-%m-%d")
                        + timedelta(days=(schedule["technical_length_days"] - 1))
                    )[:10]
                    del result["schedule"]["dates"]
                    results.append(result)

        return dumps(results)

    def _get_cabins_for_interval(self, from_date, to_date):
        from_time = datetime.strptime(from_date, "%Y-%m-%d") if from_date is not None else None
        to_time = datetime.strptime(to_date, "%Y-%m-%d") if to_date is not None else None

        self.cabin_matcher.set_interval(from_date, to_date)
        self.price_search = CruisePriceSearch(
            plpy=self.plpy,
            cruise_id=self.cruise_id,
            from_time=from_time,
            to_time=to_time,
            booking_time=self.booking_time,
            wedding_time=self.wedding_time,
            cart_summary=self.cart_summary,
            settings=self.settings,
            abstract_search=self,
        )

        available_devices = self.cabin_matcher.check()
        if available_devices is None:
            return None

        if not self.is_interval_query:
            return {"availability": available_devices, "results": None, "schedule": None}

        self.date_ranges = self._get_open_and_price_modifier_date_ranges("App\\Cruise", self.cruise_id)
        cooker = DateRangeCooker(
            plpy=self.plpy,
            date_rangeable_type="App\\Cruise",
            date_rangeable_id=self.cruise_id,
            from_time=from_time,
            to_time=to_time,
            date_ranges=self.date_ranges,
        )

        if not cooker.is_covered():
            return None

        if not self.cabin_matcher.has_common_meal_plan(self.date_ranges, "App\\Cruise", self.cruise_id):
            return None

        self._load_meal_plans("App\\Cruise", self.cruise_id)

        price_modifier_range_cooker = PriceModifierRangeCooker(
            plpy=self.plpy,
            date_rangeable_type="App\\Cruise",
            date_rangeable_id=self.cruise_id,
            from_time=from_time,
            to_time=to_time,
            date_ranges=self.date_ranges,
        )
        price_modifier_range_cooker.add_annual_price_modifiers()
        price_modifier_range_cooker.merge_multi_period_price_modifier_ranges()

        self.price_modifiers = self._get_price_modifiers(
            "App\\Cruise",
            self.cruise_id,
            self.date_ranges,
            available_devices,
            self.request_handler.age_resolver,
        )
        self.switch_wrappers = SwitchWrapper.factory(
            self.plpy,
            self.settings,
            self.price_modifiers[Config.PRICE_MODIFIER_TYPE_SWITCH],
            from_time,
            to_time,
        )
        self.combination_wrappers = CombinationWrapper.factory(
            self.plpy,
            self.settings,
            self.price_modifiers[Config.PRICE_MODIFIER_TYPE_DISCOUNT],
            from_time,
            to_time,
        )
        self.rule_wrappers = RuleWrapper.factory(
            self.plpy,
            self.settings,
            self.price_modifiers[Config.PRICE_MODIFIER_TYPE_RULE],
            from_time,
            to_time,
        )

        i = 0
        results = {}
        order_itemable_indexes = []
        for order_itemable_index in range(len(self.request_handler.request)):
            if order_itemable_index not in order_itemable_indexes:
                order_itemable_indexes.append(order_itemable_index)

            for device in available_devices:
                has_price = False
                if i in device["usage_pairs"]:
                    # check if it has price - and calculate prices with or without price_modifier
                    device_prices = self._calculate_device_prices(order_itemable_index, device["device_id"])

                    if "prices" in device_prices and device_prices["prices"] is not None:
                        has_price = True
                        results.setdefault(i, []).append(device_prices)
                    if not has_price:
                        del device
            i += 1

        if not results or order_itemable_indexes != results.keys():
            return None

        return {"availability": available_devices, "results": results}

    def _calculate_device_prices(self, order_itemable_index, productable_id):
        """
        Calculate prices for one device and one cabin request
        """
        return self._calculate_prices(order_itemable_index, "device_id", productable_id)
