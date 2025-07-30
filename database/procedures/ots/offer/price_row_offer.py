from ots.offer.offer import Offer
from ots.offer.utils.date_range_price_description import DatePriceDescription
from ots.offer.utils.offer_summary import OfferSummary
from ots.repository.model.price import Price
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel


class PriceRowOffer(Offer):
    """
    Price row offer class
    """

    def __init__(self, **keyword_parameters):
        super(PriceRowOffer, self).__init__(**keyword_parameters)

    """
    Calculate Price Row Offer
    """

    def calculate(
        self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
    ):
        Offer.calculate(
            self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
        )

        if not self.meta:
            return OfferSummary([])

        # we need to use calculated price only for the range where price modifier is applicable (not the whole)

        original_calculated_price = super(PriceRowOffer, self).get_minimum_price(
            int(room_offer["meal_plan_id"]), self.price_modifier.deduction_base_prices_of_switch, None, False
        )

        meal_plan_id = int(self.meta.get("recalculate_using_meal_plan", room_offer["meal_plan_id"]))

        product_ids = None
        if "recalculate_using_products" in self.meta:
            product_ids = self.meta["recalculate_using_products"].split(",")

        deduction_base_prices = self.get_deduction_base_prices()

        calculated_price = super(PriceRowOffer, self).get_minimum_price(
            meal_plan_id, deduction_base_prices, product_ids, False
        )

        if calculated_price is not None and original_calculated_price is not None:
            price = calculated_price - original_calculated_price

            return OfferSummary(
                [
                    DatePriceDescription(
                        date=None,  # None means there is no extra restriction to wrapper range
                        price=self.get_calculations_base_price(price.summary),
                        price_row_collection=PriceRowCollectionModel(
                            price_rows={
                                "PriceRowOfferRow": PriceRowModel(
                                    meta=None, amount=1, price=price.summary
                                )
                            }
                        ),
                    )
                ]
            )
        else:
            return OfferSummary([])
