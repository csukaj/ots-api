from ots.repository.exceptions.unsupported_operation_error import UnsupportedOperationError
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
from ots.repository.utils.object_mapper import BaseModel, HasMap


class PriceRowCollectionPerMealPlanModel(BaseModel):
    price_row_collection_per_meal_plan = HasMap(int, PriceRowCollectionModel)

    def __add__(self, other):
        if isinstance(other, PriceRowCollectionPerMealPlanModel):
            new_dict = {}

            a_meal_plan_ids = set(
                self.get("price_row_collection_per_meal_plan").keys())
            b_meal_plan_ids = set(
                other.get("price_row_collection_per_meal_plan").keys())
            meal_plan_ids = a_meal_plan_ids.union(b_meal_plan_ids)

            for meal_plan_id in meal_plan_ids:
                a = self.get("price_row_collection_per_meal_plan").get(meal_plan_id)
                b = other.get("price_row_collection_per_meal_plan").get(meal_plan_id)
                if a is None:
                    new_dict[meal_plan_id] = b
                elif b is None:
                    new_dict[meal_plan_id] = a
                else:
                    new_dict[meal_plan_id] = a + b
            return PriceRowCollectionPerMealPlanModel(price_row_collection_per_meal_plan=new_dict)
        raise UnsupportedOperationError(
            """
            Only PriceRowCollectionPerMealPlanModel + PriceRowCollectionPerMealPlanModel is allowed 
            for PriceRowCollectionPerMealPlanModel
            """
        )
