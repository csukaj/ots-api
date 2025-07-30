from datetime import datetime
from ots.price_modifier.combination_wrapper import CombinationWrapper
from ots.pricing.price_modifier_calculator import PriceModifierCalculator
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price import Price
from ots.repository.model.price_row_meta_model import PriceRowMetaModel
from ots.offer.utils.date_range_price_description import DatePriceDescription
from ots.offer.utils.offer_summary import OfferSummary
from ots.search.abstract_search import AbstractSearch
from ots.price_modifier.price_modifier import PriceModifier
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
import unittest
import mock
from copy import deepcopy
from ots.common.config import Config


class TestPriceModifierCalculator(unittest.TestCase):
    def test_get_offer_when_there_is_no_combination_wrapper(self):
        room_offer = PriceModifierCalculator(
            plpy=None,
            subject_type="App\\Device",
            subject_id=1,
            productable_type="App\\Device",
            productable_id=1,
            price_modifiable_type="App\\Organization",
            price_modifiable_id=1,
            order_itemable_index=None,
            combination_wrapper=None,
            settings={"discount_calculations_base": "rack prices"},
            meal_plans={2: None, 3: None},
            abstract_search=None,
            date_ranges=None,
        ).get_offer(
            meal_plan_id=2,
            meal_offer=PriceRowCollectionModel(price_rows={}),
            room_request={"usage": [{"age": 21, "amount": 2}]},
        )

        self.assertEqual(room_offer["meal_offer"].price_rows, {})

    def test_get_offer_when_there_is_an_empty_combination_wrapper(self):
        room_offer = PriceModifierCalculator(
            plpy=None,
            subject_type="App\\Device",
            subject_id=1,
            productable_type="App\\Device",
            productable_id=1,
            price_modifiable_type="App\\Organization",
            price_modifiable_id=1,
            order_itemable_index=None,
            combination_wrapper=CombinationWrapper(
                plpy=None,
                settings={"discount_calculations_base": "rack prices"},
                productable_type="App\\Device",
                productable_id=1,
                combination_from_time=datetime(2019, 5, 1),
                combination_to_time=datetime(2019, 5, 5),
                price_modifiers=[],
            ),
            settings={"discount_calculations_base": "rack prices"},
            meal_plans={2: None, 3: None},
            abstract_search=None,
            date_ranges=None,
        ).get_offer(
            meal_plan_id=2,
            meal_offer=PriceRowCollectionModel(price_rows={}),
            room_request={"usage": [{"age": 21, "amount": 2}]},
        )

        self.assertEqual(room_offer["meal_offer"].price_rows, {})

    def test_get_offer_when_there_is_a_non_empty_combination_wrapper_without_offer(self):
        # AbstractSearch is not clean.
        # It has complicated __init__ method.
        # We don't want to test AbstractSearch though.
        # So with this trick we can mock the __init__ up
        with mock.patch.object(AbstractSearch, "__init__", lambda x: None):
            abstract_search = AbstractSearch()
            abstract_search.taxonomies = {"1": ""}

        PriceModifierMock = deepcopy(PriceModifier)
        PriceModifierMock._load_classification = mock.Mock()
        PriceModifierMock._load_meta = mock.Mock()
        PriceModifierMock._load_offer = mock.Mock()
        PriceModifierMock.get_applicable_devices = mock.MagicMock(
            return_value=[{"device_id": 1, "usage_pairs": [{"age": 21, "amount": 2}]}]
        )

        price_modifier = PriceModifierMock(
            plpy=None,
            request=[{"usage": [{"age": 21, "amount": 2}]}, {"usage": [{"age": 21, "amount": 1}]}],
            request_from_time=datetime(2019, 5, 1),
            request_to_time=datetime(2019, 5, 10),
            date_ranges={Config.DATE_RANGE_TYPE_OPEN: [], Config.DATE_RANGE_TYPE_PRICE_MODIFIER: []},
            price_modifiable_type="App\\Organization",
            price_modifiable_id=1,
            available_devices=[{"available": 4, "is_overbooked": False, "usage_pairs": [0], "device_id": 42}],
            combination_from_time=datetime(2019, 5, 1),
            combination_to_time=datetime(2019, 5, 5),
            offer=None,
            properties={
                "type_classifications": None,
                "type_metas": None,
                "offer_taxonomy_id": "1",
                "priority": "0",
                "condition_taxonomy_id": None,
            },
            from_time=datetime(2019, 5, 1),
            to_time=datetime(2019, 5, 10),
            abstract_search=abstract_search,
            age_resolver=None,
            cart_summary=None,
        )

        room_offer = PriceModifierCalculator(
            plpy=None,
            subject_type="App\\Device",
            subject_id=1,
            productable_type="App\\Device",
            productable_id=1,
            price_modifiable_type="App\\Organization",
            price_modifiable_id=1,
            order_itemable_index=None,
            combination_wrapper=CombinationWrapper(
                plpy=None,
                settings={"discount_calculations_base": "rack prices"},
                productable_type="App\\Device",
                productable_id=1,
                combination_from_time=datetime(2019, 5, 1),
                combination_to_time=datetime(2019, 5, 5),
                price_modifiers=[price_modifier],
            ),
            settings={"discount_calculations_base": "rack prices"},
            meal_plans={2: None, 3: None},
            abstract_search=None,
            date_ranges=None,
        ).get_offer(
            meal_plan_id=2,
            meal_offer=PriceRowCollectionModel(price_rows={}),
            room_request={"usage": [{"age": 21, "amount": 2}]},
        )
        self.assertEqual(room_offer["meal_offer"].price_rows, {})

    def test_get_offer_when_there_is_a_price_modifier_with_returning_fix_modification(self):

        PriceModifierMock = deepcopy(PriceModifier)
        PriceModifierMock.__init__ = lambda x: None
        PriceModifierMock.get_info = lambda x: {}
        PriceModifierMock.is_applicable = lambda *x: True
        PriceModifierMock.copy = lambda *x: x[0]
        PriceModifierMock.get_priority = mock.MagicMock(return_value=1)
        PriceModifierMock.get_applicable_devices = mock.MagicMock(
            return_value=[{"device_id": 1, "usage_pairs": {0: {"age": 21, "amount": 2}}}]
        )
        PriceModifierMock.calculate = mock.MagicMock(
            return_value=OfferSummary(
                date_descriptions=[
                    DatePriceDescription(
                        datetime(2019, 5, 1),
                        -10,
                        PriceRowCollectionModel(
                            price_rows={
                                "Adult 2": PriceRowModel(
                                    meta=PriceRowMetaModel(), amount=2, price=Price(net=-10, rack=-10)
                                )
                            }
                        ),
                    )
                ]
            )
        )
        price_modifier = PriceModifierMock()

        setattr(
            price_modifier,
            "properties",
            {
                "type_classifications": None,
                "type_metas": None,
                "offer_taxonomy_id": "1",
                "priority": "0",
                "condition_taxonomy_id": None,
            },
        )

        room_offer = PriceModifierCalculator(
            plpy=None,
            subject_type="App\\Device",
            subject_id=1,
            productable_type="App\\Device",
            productable_id=1,
            price_modifiable_type="App\\Organization",
            price_modifiable_id=1,
            order_itemable_index=0,
            combination_wrapper=CombinationWrapper(
                plpy=None,
                settings={"discount_calculations_base": "rack prices"},
                productable_type="App\\Device",
                productable_id=1,
                combination_from_time=datetime(2019, 5, 1),
                combination_to_time=datetime(2019, 5, 5),
                price_modifiers=[price_modifier],
            ),
            settings={"discount_calculations_base": "rack prices"},
            meal_plans={2: None, 3: None},
            abstract_search=None,
            date_ranges=None,
        ).get_offer(
            meal_plan_id=2,
            meal_offer=PriceRowCollectionModel(
                price_rows={
                    "Adult 2": PriceRowModel(
                        meta=PriceRowMetaModel(), amount=2, price=Price(net=200, rack=300)
                    )
                }
            ),
            room_request={"usage": [{"age": 21, "amount": 2}]},
        )

        self.assertEqual(room_offer["discounted_meal_offer"].summary.net, 190)
        self.assertEqual(room_offer["discounted_meal_offer"].summary.rack, 290)

