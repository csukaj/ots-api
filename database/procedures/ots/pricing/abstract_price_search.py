from ots.common.config import Config
from ots.pricing.price_calculator import PriceCalculator
from ots.pricing.price_modifier_calculator import PriceModifierCalculator
from ots.pricing.switch_calculator import SwitchCalculator
from ots.price_modifier.combination_wrapper import CombinationWrapper
from ots.price_modifier.rule_wrapper import RuleWrapper
from ots.price_modifier.switch_wrapper import SwitchWrapper
from typing import List, Union


class AbstractOffer(dict):
    def __init__(self, prices, usages):
        # ({"meal_plan_id": int, "meal_offer": PriceRowCollectionModel}, object) -> None
        super(AbstractOffer, self).__init__(prices=prices, usages=usages)
        self.prices = prices
        self.usages = usages


class AbstractPriceSearch(object):
    def __init__(self, **keyword_parameters):
        self.plpy = keyword_parameters.get("plpy")
        self.from_time = keyword_parameters.get("from_time")
        self.to_time = keyword_parameters.get("to_time")
        self.booking_time = keyword_parameters.get("booking_time")
        self.wedding_time = keyword_parameters.get("wedding_time")
        self.settings = keyword_parameters.get("settings")
        self.abstract_search = keyword_parameters.get("abstract_search")
        self.cart_summary = keyword_parameters.get("cart_summary")
        self.remove_request = keyword_parameters.get("remove_request", True)

        # unfortunately these params are made for reducing function arguments but these shouldn't be here
        self.params = None
        self.open_date_ranges = None
        self.order_itemable_index = None

        self.combination_wrapper = None  # type: Union[CombinationWrapper, None]
        self.switch_wrapper = None  # type: Union[List[SwitchWrapper], None]
        self.rule_wrapper = None  # type: Union[RuleWrapper, None]

    def find(
        self,
        order_itemable_index,
        room_request,
        device_id,
        combination_wrapper,
        open_date_ranges,
        switch_wrappers=None,
        rule_wrappers=None,
    ):
        # type: (...) -> AbstractOffer
        return AbstractOffer(None, None)

    def _get_abstract_offers(self, params):
        # type: (dict) -> AbstractOffer or None

        if (
            self.combination_wrapper is not None
            and type(self.combination_wrapper) is not list
            and self.combination_wrapper.combination_from_time
        ):
            price_from_time = self.combination_wrapper.combination_from_time
            price_to_time = self.combination_wrapper.combination_to_time
        else:
            price_from_time = self.from_time
            price_to_time = self.to_time

        date_ranges = {
            Config.DATE_RANGE_TYPE_OPEN: self.open_date_ranges,
            Config.DATE_RANGE_TYPE_PRICE_MODIFIER: None,
        }

        room_offers = PriceCalculator(
            plpy=self.plpy,
            from_time=price_from_time,
            to_time=price_to_time,
            productable_type=params["productable_type"],
            productable_id=params["productable_id"],
            price_modifiable_type=params["price_modifiable_type"],
            price_modifiable_id=params["price_modifiable_id"],
            date_ranges=date_ranges,
            age_resolver=self.abstract_search.request_handler.age_resolver,
        ).get_offers(params["request"])

        if room_offers is None:
            return None

        switch_calculator = SwitchCalculator(
            subject_type=params["subject_type"],
            subject_id=params["subject_id"],
            productable_type=params["productable_type"],
            productable_id=params["productable_id"],
            price_modifiable_type=params["price_modifiable_type"],
            price_modifiable_id=params["price_modifiable_id"],
            order_itemable_index=self.order_itemable_index,
            switch_wrappers=(
                [wrapper for wrapper in self.switch_wrapper if wrapper is not None]
                if type(self.switch_wrapper) is list
                else []
            ),
            settings=self.settings,
            meal_plans=self.abstract_search.meal_plans,
        )

        price_modifier_calculator = PriceModifierCalculator(
            plpy=self.plpy,
            subject_type=params["subject_type"],
            subject_id=params["subject_id"],
            productable_type=params["productable_type"],
            productable_id=params["productable_id"],
            price_modifiable_type=params["price_modifiable_type"],
            price_modifiable_id=params["price_modifiable_id"],
            order_itemable_index=self.order_itemable_index,
            combination_wrapper=self.combination_wrapper,
            switch_wrapper=self.switch_wrapper,
            settings=self.settings,
            meal_plans=self.abstract_search.meal_plans,
            abstract_search=self.abstract_search,
            date_ranges=date_ranges,
        )

        final_room_offers = []

        price_row_collection_per_meal_plan = room_offers.offers.get("price_row_collection_per_meal_plan")

        if price_row_collection_per_meal_plan == {}:
            return AbstractOffer(prices=None, usages=None)

        for meal_plan_id, meal_offer in price_row_collection_per_meal_plan.iteritems():
            offer_with_switches = switch_calculator.get_offer(
                meal_plan_id, meal_offer, room_offers.room_request
            )
            offer_with_switches_and_rules_and_discounts = price_modifier_calculator.get_offer(
                meal_plan_id, offer_with_switches, room_offers.room_request
            )

            if offer_with_switches_and_rules_and_discounts is not None:
                final_room_offers.append(offer_with_switches_and_rules_and_discounts)

        return AbstractOffer(
            prices=sorted(final_room_offers, key=lambda offer: offer["meal_plan_id"]),
            usages=room_offers.usage,
        )
