from ots.offer.offer import Offer
from ots.offer.utils.date_range_price_description import DatePriceDescription
from ots.offer.utils.offer_summary import OfferSummary
from ots.repository.model.price import Price
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel


class TieredPriceOffer(Offer):
    """
    Tiered price offer class
    """

    def __init__(self, **keyword_parameters):
        super(TieredPriceOffer, self).__init__(**keyword_parameters)

    """
    Calculate tiered price offer
    """

    def calculate(
        self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
    ):
        Offer.calculate(
            self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
        )

        if self.is_meta_empty("fixed_value") and self.is_meta_empty("pax_value"):
            return OfferSummary([])

        try:
            fixed_value = int(self.meta.get("fixed_value", "0"))
            pax_value_from_headcount = int(self.meta.get("pax_value_from_headcount", "1"))
            pax_value = int(self.meta.get("pax_value", "0"))
        except ValueError:
            return OfferSummary([])

            # we need to use calculated price only for the range where price modifier is applicable (not the whole)
        original_calculated_price = super(TieredPriceOffer, self).get_minimum_price(
            int(room_offer["meal_plan_id"]), self.price_modifier.deduction_base_prices_of_switch, None, False
        )

        modifier_sum = fixed_value
        headcount = self.abstract_search.request_handler.get_pax()

        if headcount >= pax_value_from_headcount:
            headcount_difference = headcount - pax_value_from_headcount + 1
            modifier_sum += headcount_difference * pax_value

        price = modifier_sum * self.get_nights() - self.get_calculations_base_price(original_calculated_price.summary)

        return OfferSummary(
            [
                DatePriceDescription(
                    date=None,  # None means there is no extra restriction to wrapper range
                    price=price,
                    price_row_collection=PriceRowCollectionModel(
                        price_rows={
                            "TieredPriceOfferRow": PriceRowModel(
                                meta=None, amount=1, price=Price(net=price, rack=price)
                            )
                        }
                    ),
                )
            ]
        )
