from ots.repository.model.price import Price
from ots.repository.model.price_row_meta_model import PriceRowMetaModel
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
import unittest
from ots.repository.utils.object_mapper import ObjectMapper
from ots.repository.model.price_row_collection_per_meal_plan_model import (
    PriceRowCollectionPerMealPlanModel,
)


class TestObjectMapperIntegration(unittest.TestCase):
    def test_real_scenario(self):
        object_to_map = {
            "2": {
                'price_rows': {
                    "Single Adult": {
                        "amount": 1,
                        "meta": {
                            "age_range": "adult",
                            "mandatory": False,
                            "product_id": 27,
                            "extra": False,
                            "product_type_taxonomy_id": 60,
                            "price_name": "Single Adult",
                            "amount": 1,
                            "non_empty_date_ranges": [103, 104, 105],
                            "id": 57,
                            "productable_id": 25,
                        },
                        "net": 478.2,
                        "rack": 550.0,
                    }
                }
            }
        }
        
        mapped = ObjectMapper().map(
            {'price_row_collection_per_meal_plan': object_to_map},
            PriceRowCollectionPerMealPlanModel,
        )

        self.assertIsInstance(mapped, PriceRowCollectionPerMealPlanModel)
        self.assertIsInstance(mapped.get(
            'price_row_collection_per_meal_plan'), dict)
        self.assertIsInstance(mapped.get('price_row_collection_per_meal_plan')[
                              2], PriceRowCollectionModel)
        self.assertIsInstance(mapped.get(
            'price_row_collection_per_meal_plan')[2].price_rows, dict)
        self.assertIsInstance(mapped.get('price_row_collection_per_meal_plan')[
                              2].price_rows["Single Adult"], PriceRowModel)
        self.assertIsInstance(mapped.get('price_row_collection_per_meal_plan')[
                              2].price_rows["Single Adult"].get('meta'), PriceRowMetaModel)
        self.assertIsInstance(mapped.get('price_row_collection_per_meal_plan')[
                              2].price_rows["Single Adult"].get('price'), Price)
        self.assertEqual(mapped.get('price_row_collection_per_meal_plan')[
                         2].price_rows["Single Adult"].get('amount'), 1)
        self.assertEqual(mapped.get('price_row_collection_per_meal_plan')[
                         2].price_rows["Single Adult"].get('price'), Price(net=478.2, rack=550.0))
