from copy import deepcopy
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
from ots.offer.utils.offer_summary import OfferSummary
from stylers.date_helpers import segmented_nights, datetimestr_to_datetime


class SwitchCalculator:
    def __init__(self, **keyword_parameters):
        # The price_modifier's date_range:
        # type: Union[datetime.datetime, None]
        self.from_time = keyword_parameters.get("from_time")
        # type: Union[datetime.datetime, None]
        self.to_time = keyword_parameters.get("to_time")

        # For price modifier:
        self.subject_type = keyword_parameters["subject_type"]
        self.subject_id = keyword_parameters["subject_id"]
        self.productable_type = keyword_parameters["productable_type"]
        self.productable_id = keyword_parameters["productable_id"]

        self.price_modifiable_type = keyword_parameters["price_modifiable_type"]
        self.price_modifiable_id = keyword_parameters["price_modifiable_id"]
        self.order_itemable_index = keyword_parameters["order_itemable_index"]
        self.switch_wrappers = keyword_parameters["switch_wrappers"]
        self.settings = keyword_parameters["settings"]
        self.meal_plans = keyword_parameters["meal_plans"]
        self.deduction_base_prices = keyword_parameters.get("deduction_base_prices")

    def get_offer(self, meal_plan_id, meal_offer, room_request):
        # type: (int, PriceRowCollectionModel, dict) -> PriceRowCollectionModel

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
            "order_itemable_index": self.order_itemable_index,
            "room_request": room_request,
            "discounts": [],
            "switches": [],
        }

        calculated_price_modification_info_list = [
            self._get_price_modification_info(
                (
                    max(self.from_time, wrapper.combination_from_time)
                    if self.from_time
                    else wrapper.combination_from_time
                ),
                (
                    min(self.to_time, wrapper.combination_to_time)
                    if self.to_time
                    else wrapper.combination_to_time
                ),
                price_modifier,
                room_offer,
            )
            for wrapper in self.switch_wrappers
            for price_modifier in wrapper.price_modifiers
            if self._is_applicable(price_modifier.get_applicable_devices())
            if self._has_common_nights_with(wrapper)
        ]

        calculated_price_modification_info_list = [
            i for i in calculated_price_modification_info_list if i is not None
        ]

        sum_of_modifications = self._get_sum_of_modifications(calculated_price_modification_info_list)
        room_offer["switches"] = calculated_price_modification_info_list

        modified_meal_offer = meal_offer + sum_of_modifications
        return modified_meal_offer

    def _get_sum_of_modifications(self, calculated_price_modifier_info_list):
        # type: (List[dict]) -> PriceRowCollectionModel
        return sum(
            (info["offer_summary"].price_row_collection for info in calculated_price_modifier_info_list),
            PriceRowCollectionModel(price_rows={}),
        )

    def _get_price_modification_info(self, from_time, to_time, price_modifier, room_offer):
        price_modifier = price_modifier.copy(
            {
                "deduction_base_prices_of_switch": self.deduction_base_prices,
                "combination_from_time": from_time,
                "combination_to_time": to_time,
            }
        )

        # type: PriceRowCollectionModel
        calculated_modification = price_modifier.calculate(
            room_offer, self.productable_type, self.productable_id, self.subject_type, self.subject_id, False
        )

        if calculated_modification is not None:
            price_modifier_info = price_modifier.get_info()
            price_modifier_info["discount_value"] = calculated_modification.price
            price_modifier_info["offer_summary"] = calculated_modification
            room_offer["switches"].append(price_modifier_info)

            return price_modifier_info
        return None

    def _has_common_nights_with(self, wrapper):
        if self.to_time and self.from_time:
            from_time = wrapper.combination_from_time
            to_time = wrapper.combination_to_time
            return segmented_nights(self.from_time, self.to_time, from_time, to_time) > 0

        return True  # if we cant check, we belive it has

    def _is_applicable(self, applicable_rooms):
        if self.subject_type != "App\\Device":
            return True

        for room in applicable_rooms:
            if room["device_id"] == self.subject_id:
                return self.order_itemable_index in room["usage_pairs"]

        return False
