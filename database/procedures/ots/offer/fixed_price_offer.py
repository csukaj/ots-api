from ots.repository.model.price import Price
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
from ots.offer.offer import Offer
from json import loads
from ots.offer.utils.offer_summary import OfferSummary
from ots.offer.utils.date_range_price_description import DatePriceDescription


class FixedPriceOffer(Offer):
    """
    Percentage offer class
    """

    def __init__(self, **keyword_parameters):
        super(FixedPriceOffer, self).__init__(**keyword_parameters)

    """
    Calculate Percentage Offer
    """

    def calculate(
        self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
    ):
        Offer.calculate(
            self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
        )

        if self.is_meta_empty("modifier_value"):
            return OfferSummary([])

        try:
            modifier_dict = loads(self.meta["modifier_value"])
            if not modifier_dict or type(modifier_dict) is not dict:
                return OfferSummary([])

            named_request_usage = self.age_resolver.resolve_room_usage(
                room_offer["room_request"]["usage"], True
            )

            modifier_sum = 0
            for modifier_age_key, modifier_age_value in modifier_dict.iteritems():
                if modifier_age_key in named_request_usage:
                    modifier_sum += named_request_usage[modifier_age_key] * float(modifier_age_value)

            return OfferSummary(
                [
                    DatePriceDescription(
                        date=None,  # None means there is no extra restriction to wrapper range
                        price=modifier_sum,
                        price_row_collection=PriceRowCollectionModel(
                            price_rows={
                                # fixed price offer does not have exact price row to decrease
                                # so it will add a new price row
                                "FixedPriceOfferRow": PriceRowModel(
                                    meta=None, amount=1, price=Price(net=modifier_sum, rack=modifier_sum)
                                )
                            }
                        ),
                    )
                ]
            )

        except ValueError:
            return OfferSummary([])
