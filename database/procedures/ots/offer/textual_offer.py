from ots.offer.offer import Offer
from ots.offer.utils.date_range_price_description import DatePriceDescription
from ots.offer.utils.offer_summary import OfferSummary
from ots.repository.model.price import Price
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel


class TextualOffer(Offer):
    """
    Textual offer class
    """

    def __init__(self, **keyword_parameters):
        super(TextualOffer, self).__init__(**keyword_parameters)

    def calculate(
        self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
    ):
        Offer.calculate(
            self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
        )

        return OfferSummary(
            [
                DatePriceDescription(
                    date=None,  # None means there is no extra restriction to wrapper range
                    price=0,
                    price_row_collection=PriceRowCollectionModel(
                        price_rows={
                            "TextualOfferRow": PriceRowModel(
                                meta=None, amount=1, price=Price(net=0, rack=0)
                            )
                        }
                    ),
                )
            ]
        )
