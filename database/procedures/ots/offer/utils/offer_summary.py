from ots.repository.model.price_row_collection_model import PriceRowCollectionModel


class OfferSummary(dict):
    def __init__(self, date_descriptions=()):
        # type: (list or tuple) -> None
        self.price = self._calculate_summary(date_descriptions)

        # TODO: delete price because it could be determined by price_row_collection
        self.price_row_collection = self._calculate_price_row_collection_summary(date_descriptions)
        self.date_descriptions = date_descriptions

        super(OfferSummary, self).__init__(price=self.price, date_descriptions=self.date_descriptions)

    def _calculate_summary(self, date_descriptions):
        return sum(d.price for d in date_descriptions)

    def _calculate_price_row_collection_summary(self, date_descriptions):
        return sum(
            (d.price_row_collection for d in date_descriptions), PriceRowCollectionModel(price_rows={})
        )
