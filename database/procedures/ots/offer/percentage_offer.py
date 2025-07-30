from copy import deepcopy
from ots.offer.offer import Offer
from ots.offer.utils.date_range_price_description import DatePriceDescription
from ots.offer.utils.offer_summary import OfferSummary
from ots.pricing.price_calculator import PriceRepository
from stylers.date_helpers import datetimestr_to_datetime
from ots.repository.model.price import Price
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel


class PercentageOffer(Offer):
    """
    Percentage offer class
    """

    def __init__(self, **keyword_parameters):
        super(PercentageOffer, self).__init__(**keyword_parameters)

    """
    Calculate Percentage Offer
    """

    @property
    def should_apply_previous_price_modifiers(self):
        return "do_not_apply_previous_price_modifiers" not in self.classification

    def calculate(
        self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
    ):
        Offer.calculate(
            self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
        )

        if self.is_meta_empty("modifier_percentage"):
            return OfferSummary([])

        composed_deduction_base_prices = self.get_deduction_base_prices()

        price_for_deduction = super(PercentageOffer, self).get_minimum_price(
            int(self.meta.get("deduction_base_meal_plan_id", room_offer["meal_plan_id"])),
            composed_deduction_base_prices,
            None,
            False,
        )  # type : Price

        if price_for_deduction is None:
            return OfferSummary([])

        if self.should_apply_previous_price_modifiers:
            for applied_price_modifier in room_offer["discounts"] + room_offer.setdefault("switches", []):
                # applied_price_modifier can be rule, discount or switch
                if composed_deduction_base_prices:
                    # the current calculated deduction base price is differ from the percentage settings
                    recalculated_discount = self._find_and_recalculate_price_modifier(
                        searched_id=applied_price_modifier["id"],
                        room_offer=room_offer,
                        deduction_base_prices=composed_deduction_base_prices,
                    )
                    if recalculated_discount is None:
                        recalculated_discount = applied_price_modifier["offer_summary"]
                    summary = recalculated_discount
                else:
                    # the current calculated deduction base price is right we don't need to calculate again
                    summary = applied_price_modifier["offer_summary"]
                
                modification = self._filter_summary_to_date_range(summary).price_row_collection
                price_for_deduction += modification

        try:
            coeficient = float(self.meta["modifier_percentage"]) / 100.0
            price_for_deduction = price_for_deduction.get_without_negative_price_rows()
            price = price_for_deduction * coeficient
            return OfferSummary(
                [
                    DatePriceDescription(
                        date=None,  # None means there is no extra restriction to wrapper range
                        price=self.get_calculations_base_price(price.summary),
                        price_row_collection=price,
                    )
                ]
            )
        except ValueError:
            return OfferSummary([])

    def _find_and_recalculate_price_modifier(self, searched_id, room_offer, deduction_base_prices):
        if not self.price_modifier.all_price_modifiers_in_combination:
            return None
        found_price_modifier = None
        for price_modifier in self.price_modifier.all_price_modifiers_in_combination:
            if price_modifier.get_id() == searched_id:
                found_price_modifier = price_modifier
        if not found_price_modifier:
            return None
        room_offer2 = deepcopy(room_offer)

        room_offer2["room_request"]["usage"] = self._transform_deduction_base_usages_to_usage_request(
            deduction_base_prices
        )
        room_offer2["discounts"] = []
        room_offer2["switches"] = []

        for discount in room_offer["discounts"]:
            if discount["id"] == searched_id:
                break

            room_offer2["discounts"].append(discount)

        for switch in room_offer.setdefault("switches", []):
            if switch["id"] == searched_id:
                break

            room_offer2["switches"].append(switch)

            # do we need the recalculated price modifier to be calculated on the deduction base meal plan?
        # room_offer2['meal_plan_id'] = self.meta.get('deduction_base_meal_plan_id', room_offer['meal_plan_id'])
        room_offer2["discounted_price"] = room_offer2["original_price"]
        x = found_price_modifier.calculate(
            room_offer2,
            self.productable_type,
            self.productable_id,
            self.subject_type,
            self.subject_id,
            self.price_modifier.all_price_modifiers_in_combination,
        )

        return x

    def _transform_deduction_base_usages_to_usage_request(self, deduction_base_prices):
        prices = PriceRepository(self.plpy).get_prices(
            True, None, self.productable_type, self.productable_id
        )  # TODO: Can we use None?
        deducted_age_ranges = []
        for k, v in deduction_base_prices.iteritems():
            prices = [price for price in prices if price["price_name"] == k]
            if prices:
                found_price = prices[0]
                age_range = self.age_resolver.get_age_range_by_name(found_price["age_range"])
                deducted_age_ranges.append(
                    {"age": age_range["from_age"], "amount": v * found_price["amount"]}
                )
        return deducted_age_ranges

    def _filter_summary_to_date_range(self, summary):
        # type: (OfferSummary) -> OfferSummary

        filtered_date_range_describers = filter(
            self._is_date_description_in_this_range, summary.date_descriptions
        )

        return OfferSummary(filtered_date_range_describers)

    def _is_date_description_in_this_range(self, date_price_description):
        # type: (DatePriceDescription) -> bool

        from_time = datetimestr_to_datetime(self.from_time)
        to_time = datetimestr_to_datetime(self.to_time)

        if from_time is None:
            raise TypeError("percentage offer cannot be determined because from_time is wrong")
        if to_time is None:
            raise TypeError("percentage offer cannot be determined because to_time is wrong")

        if not date_price_description.date:
            # if there is no date range given, it doesn't need to be filtered
            return True

        date = datetimestr_to_datetime(date_price_description.date)

        if date is None:
            raise TypeError("percentage offer cannot be determined because date is wrong")

        return from_time <= date <= to_time
