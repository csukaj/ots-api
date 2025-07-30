from unittest import TestCase
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_meta_model import PriceRowMetaModel
from ots.repository.model.price import Price


class TestPriceRowModel(TestCase):
    def setUp(self):
        super(TestPriceRowModel, self).setUp()

        self.price_row_model = PriceRowModel(
            amount=1, meta=PriceRowMetaModel(), price=Price(net=200, rack=300)
        )

    def test_addition(self):
        price = Price(net=200, rack=300)

        new_price_row = self.price_row_model + price

        self.assertEqual(new_price_row.get("price").net, 400)
        self.assertEqual(new_price_row.get("price").rack, 600)
