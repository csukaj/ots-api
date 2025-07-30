from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
from unittest import TestCase
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_meta_model import PriceRowMetaModel
from ots.repository.model.price import Price


class TestPriceRowCollectionModel(TestCase):
    def test_addition_when_the_price_rows_are_same(self):
        a = PriceRowCollectionModel(
            price_rows={
                "Adult 2": PriceRowModel(meta=PriceRowMetaModel(), amount=2, price=Price(net=200, rack=300))
            }
        )

        b = PriceRowCollectionModel(
            price_rows={
                "Adult 2": PriceRowModel(meta=PriceRowMetaModel(), amount=2, price=Price(net=200, rack=300))
            }
        )

        new_price_row_collection = a + b

        self.assertEqual(new_price_row_collection.summary.net, 400)
        self.assertEqual(new_price_row_collection.summary.rack, 600)

        self.assertEqual(new_price_row_collection.get("price_rows")["Adult 2"].price.net, 400)
        self.assertEqual(new_price_row_collection.get("price_rows")["Adult 2"].price.rack, 600)

    def test_addition_when_the_price_rows_are_different(self):
        a = PriceRowCollectionModel(
            price_rows={
                "Adult 2": PriceRowModel(meta=PriceRowMetaModel(), amount=2, price=Price(net=200, rack=300))
            }
        )

        b = PriceRowCollectionModel(
            price_rows={
                "Extra Child": PriceRowModel(
                    meta=PriceRowMetaModel(), amount=2, price=Price(net=200, rack=300)
                )
            }
        )

        new_price_row_collection = a + b

        self.assertEqual(new_price_row_collection.summary.net, 400)
        self.assertEqual(new_price_row_collection.summary.rack, 600)

        self.assertEqual(new_price_row_collection.get("price_rows")["Adult 2"].price.net, 200)
        self.assertEqual(new_price_row_collection.get("price_rows")["Adult 2"].price.rack, 300)

        self.assertEqual(new_price_row_collection.get("price_rows")["Extra Child"].price.net, 200)
        self.assertEqual(new_price_row_collection.get("price_rows")["Extra Child"].price.rack, 300)

    def test_subtraction_when_the_price_rows_are_same(self):
        a = PriceRowCollectionModel(
            price_rows={
                "Adult 2": PriceRowModel(meta=PriceRowMetaModel(), amount=2, price=Price(net=200, rack=300))
            }
        )

        b = PriceRowCollectionModel(
            price_rows={
                "Adult 2": PriceRowModel(meta=PriceRowMetaModel(), amount=2, price=Price(net=100, rack=200))
            }
        )

        new_price_row_collection = a - b

        self.assertEqual(new_price_row_collection.summary.net, 100)
        self.assertEqual(new_price_row_collection.summary.rack, 100)

        self.assertEqual(new_price_row_collection.get("price_rows")["Adult 2"].price.net, 100)
        self.assertEqual(new_price_row_collection.get("price_rows")["Adult 2"].price.rack, 100)

    def test_subtraction_when_the_price_rows_are_different(self):
        a = PriceRowCollectionModel(
            price_rows={
                "Adult 2": PriceRowModel(meta=PriceRowMetaModel(), amount=2, price=Price(net=200, rack=300))
            }
        )

        b = PriceRowCollectionModel(
            price_rows={
                "Extra Child": PriceRowModel(
                    meta=PriceRowMetaModel(), amount=2, price=Price(net=200, rack=300)
                )
            }
        )

        new_price_row_collection = a - b

        self.assertEqual(new_price_row_collection.summary.net, 0)
        self.assertEqual(new_price_row_collection.summary.rack, 0)

        self.assertEqual(new_price_row_collection.get("price_rows")["Adult 2"].price.net, 200)
        self.assertEqual(new_price_row_collection.get("price_rows")["Adult 2"].price.rack, 300)

        self.assertEqual(new_price_row_collection.get("price_rows")["Extra Child"].price.net, -200)
        self.assertEqual(new_price_row_collection.get("price_rows")["Extra Child"].price.rack, -300)
