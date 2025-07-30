class DatePriceDescription(dict):
    def __init__(self, date, price, price_row_collection):
        self.date = date  # the date for this price

        # TODO: delete price because it could be determined by price_row_collection
        self.price = price  # nightly
        self.price_row_collection = price_row_collection
        super(DatePriceDescription, self).__init__(date=str(date), price=price)
