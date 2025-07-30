from copy import deepcopy

from ots.offer.free_nights_offer import FreeNightsOffer
from ots.price_modifier.switch_wrapper import SwitchWrapper
from ots.pricing.price_calculator import PriceCalculator
from ots.pricing.switch_calculator import SwitchCalculator
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
from stylers.date_helpers import datetimestr_to_datetime


class PriceModifierCalculator:
    def __init__(self, **keyword_parameters):
        self.plpy = keyword_parameters["plpy"]
        self.subject_type = keyword_parameters["subject_type"]
        self.subject_id = keyword_parameters["subject_id"]
        self.productable_type = keyword_parameters["productable_type"]
        self.productable_id = keyword_parameters["productable_id"]
        self.price_modifiable_type = keyword_parameters["price_modifiable_type"]
        self.price_modifiable_id = keyword_parameters["price_modifiable_id"]
        self.order_itemable_index = keyword_parameters["order_itemable_index"]
        self.combination_wrapper = keyword_parameters["combination_wrapper"]
        self.settings = keyword_parameters["settings"]
        self.meal_plans = keyword_parameters["meal_plans"]
        self.abstract_search = keyword_parameters["abstract_search"]
        self.date_ranges = keyword_parameters["date_ranges"]
        self.switch_wrapper = keyword_parameters.get("switch_wrapper")

        if self.combination_wrapper is not None and type(self.combination_wrapper) is not list:
        
            self.comination_from_time = (
                datetimestr_to_datetime(self.combination_wrapper.combination_from_time)
                if self.combination_wrapper.combination_from_time is not None
                else None
            )
            self.combination_to_time = (
                datetimestr_to_datetime(self.combination_wrapper.combination_to_time)
                if self.combination_wrapper.combination_to_time is not None
                else None
            )

    def get_offer(self, meal_plan_id, meal_offer, room_request):
        # type: (int, PriceRowCollectionModel, dict) -> dict

        if self.switch_wrapper is not None:
            meal_offer = self._get_recalculate_meal_offer_with_switches(meal_plan_id, room_request)
            if meal_offer is None:
                return None

        room_offer = {
            "meal_plan_id": meal_plan_id,  # TODO: delete because unneccessary if we ha meal_plan
            "meal_plan": (
                self.meal_plans[str(meal_plan_id)] if str(meal_plan_id) in self.meal_plans else None
            ),
            "meal_offer": meal_offer,
            "original_price": meal_offer.summary.rack,  # TODO: delete because unneccessary if we ha meal_offer
            "discounted_price": (
                meal_offer.summary.rack
                if self.settings["discount_calculations_base"] == "rack prices"
                else meal_offer.summary.net
            ),
            "margin": meal_offer.summary.margin,
            "order_itemable_index": self.order_itemable_index,
            "room_request": room_request,
            "discounts": [],
            "switches": [], # TODO: it would be good to get switches
            "period": {"date_from": None, "date_to": None},
            "has_merged_free_nights": False,
        }

        if self.combination_wrapper is not None and type(self.combination_wrapper) is not list:

            calculated_price_modification_info_list = [
                self._get_price_modification_info(price_modifier, room_offer)
                for price_modifier in self.combination_wrapper.price_modifiers
                if self._is_applicable(price_modifier.get_applicable_devices())
            ]

            calculated_price_modification_info_list = [
                i for i in calculated_price_modification_info_list if i is not None
            ]

            sum_of_modifications = self._get_sum_of_modifications(calculated_price_modification_info_list)
            discounted_price = meal_offer + sum_of_modifications
            room_offer.update(
                {
                    "discounts": [
                        info
                        for info in calculated_price_modification_info_list
                        if info["discount_value"] != 0 or info["offer"] == "textual"
                    ],
                    "period": {
                        "date_from": self.combination_wrapper.combination_from_time,
                        "date_to": self.combination_wrapper.combination_to_time,
                    },
                    "discounted_price": (
                        discounted_price.summary.rack
                        if self.settings["discount_calculations_base"] == "rack prices"
                        else discounted_price.summary.net
                    ),
                    "discounted_meal_offer": discounted_price,
                    "has_merged_free_nights": (
                        len(self.combination_wrapper.price_modifiers) == 1
                        and self.combination_wrapper.price_modifiers[0].get_condition()
                        == "merged_free_nights"
                    ),
                }
            )

        return room_offer

    def _get_sum_of_modifications(self, calculated_price_modifier_info_list):
        # type: (List[dict]) -> PriceRowCollectionModel
        return sum(
            (info["offer_summary"].price_row_collection for info in calculated_price_modifier_info_list),
            PriceRowCollectionModel(price_rows={}),
        )

    def _get_price_modification_info(self, price_modifier, room_offer):
        # type: PriceRowCollectionModel
        calculated_modification = price_modifier.calculate(
            room_offer,
            self.productable_type,
            self.productable_id,
            self.subject_type,
            self.subject_id,
            True,
            self.combination_wrapper.price_modifiers,
        )

        if calculated_modification is not None:
            price_modifier_info = price_modifier.get_info()
            price_modifier_info["discount_value"] = calculated_modification.price
            price_modifier_info["offer_summary"] = calculated_modification
            room_offer['discounts'].append(price_modifier_info)
            return price_modifier_info
        return None

    def _get_recalculate_meal_offer_with_switches(self, meal_plan_id, room_request):
        # type: (...) -> PriceRowCollectionModel
        room_offers = PriceCalculator(
            plpy=self.plpy,
            from_time=self.combination_wrapper.combination_from_time,
            to_time=self.combination_wrapper.combination_to_time,
            productable_type=self.subject_type,
            productable_id=self.subject_id,
            price_modifiable_type=self.price_modifiable_type,
            price_modifiable_id=self.price_modifiable_id,
            date_ranges=self.date_ranges,
            age_resolver=self.abstract_search.request_handler.age_resolver,
        ).get_offers(room_request)

        if room_offers is None or meal_plan_id not in room_offers.offers.get(
            "price_row_collection_per_meal_plan"
        ):
            return None

        switch_calculator = SwitchCalculator(
            from_time=self.comination_from_time,
            to_time=self.combination_to_time,
            subject_type=self.subject_type,
            subject_id=self.subject_id,
            productable_type=self.productable_type,
            productable_id=self.productable_id,
            price_modifiable_type=self.price_modifiable_type,
            price_modifiable_id=self.price_modifiable_id,
            order_itemable_index=self.abstract_search.price_search.order_itemable_index,
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
        )

        modified_meal_offer = switch_calculator.get_offer(
            meal_plan_id,
            room_offers.offers.get("price_row_collection_per_meal_plan")[meal_plan_id],
            room_request,
        )

        return modified_meal_offer

    def _is_applicable(self, applicable_rooms):
        if self.subject_type != "App\\Device":
            return True

        for room in applicable_rooms:
            if room["device_id"] == self.subject_id:
                return self.order_itemable_index in room["usage_pairs"]

        return False
