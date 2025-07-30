from ots.repository.model.price import Price
from ots.repository.model.price_row_meta_model import PriceRowMetaModel
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
from ots.repository.model.price_row_collection_per_meal_plan_model import PriceRowCollectionPerMealPlanModel
from mock import MagicMock
from ots.pricing.abstract_price_search import AbstractPriceSearch
from ots.pricing.price_calculator import PriceCalculator
from ots.pricing.switch_calculator import SwitchCalculator
from ots.pricing.price_modifier_calculator import PriceModifierCalculator
from ots.common.usage_request_handler import UsageRequestHandler
from ots.search.abstract_search import AbstractSearch
import unittest


class TestAbstractPriceSearch(unittest.TestCase):
    def test_get_abstract_offers(self):
        # Mock init functions
        PriceCalculator.__init__ = lambda *x, **y: None
        SwitchCalculator.__init__ = lambda *x, **y: None
        PriceModifierCalculator.__init__ = lambda *x, **y: None
        AbstractSearch.__init__ = lambda *x, **y: None

        PriceCalculator.get_offers = MagicMock(
            return_value=PriceCalculator.Offers(
                room_request=None,
                usage=None,
                offers=PriceRowCollectionPerMealPlanModel(
                    price_row_collection_per_meal_plan={
                        2: PriceRowCollectionModel(
                            price_rows={
                                "Adult 2": PriceRowModel(
                                    meta=PriceRowMetaModel(), amount=2, price=Price(net=4, rack=6)
                                )
                            }
                        )
                    }
                ),
            )
        )

        SwitchCalculator.get_offer = MagicMock(
            return_value=PriceRowCollectionModel(
                price_rows={
                    "Adult 2": PriceRowModel(meta=PriceRowMetaModel(), amount=2, price=Price(net=3, rack=5))
                }
            )
        )

        PriceModifierCalculator.get_offer = MagicMock(
            return_value=PriceRowCollectionModel(
                price_rows={
                    "Adult 2": PriceRowModel(meta=PriceRowMetaModel(), amount=2, price=Price(net=2, rack=4))
                }
            )
        )

        abstract_price_search = AbstractPriceSearch()
        setattr(abstract_price_search, "abstract_search", AbstractSearch())
        setattr(abstract_price_search.abstract_search, "meal_plans", None)
        setattr(
            abstract_price_search.abstract_search, "request_handler", UsageRequestHandler(None, None, None)
        )
        setattr(abstract_price_search.abstract_search, "age_resolver", None)

        offers = abstract_price_search._get_abstract_offers(
            {
                "productable_type": None,
                "productable_id": None,
                "price_modifiable_type": None,
                "price_modifiable_id": None,
                "request": None,
                "subject_type": None,
                "subject_id": None,
            }
        )

        self.assertEqual(offers.prices[0]["meal_offer"].summary, Price(net=2, rack=4))
