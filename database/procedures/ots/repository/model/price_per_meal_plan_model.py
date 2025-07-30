from ots.repository.utils.object_mapper import BaseModel, HasMap
from ots.repository.model.price import Price
from ots.repository.exceptions.unsupported_operation_error import UnsupportedOperationError


def init_price_per_meal_plan(old_dict):
    return {
        int(meal_plan_id): Price(net=price["net"], rack=price["rack"])
        for meal_plan_id, price in old_dict.iteritems()
    }


class PricePerMealPlanModel(BaseModel):
    # int key would probably break things
    price_per_meal_plan = HasMap(int, Price, converter=init_price_per_meal_plan)

    def get_net_offer(self):
        return {
            meal_plan_id: price.net for meal_plan_id, price in self.get("price_per_meal_plan").iteritems()
        }

    def get_rack_offer(self):
        return {
            meal_plan_id: price.rack for meal_plan_id, price in self.get("price_per_meal_plan").iteritems()
        }

    def __add__(self, other):
        if isinstance(other, PricePerMealPlanModel):
            summary = {}
            a_keys = set(self.get("price_per_meal_plan").keys())
            b_keys = set(other.get("price_per_meal_plan").keys())
            keys = a_keys.union(b_keys)
            for key in keys:
                a = self.get("price_per_meal_plan").get(key, Price(net=0, rack=0))
                b = other.get("price_per_meal_plan").get(key, Price(net=0, rack=0))
                summary[key] = a + b
            return PricePerMealPlanModel(price_per_meal_plan=summary)
        raise UnsupportedOperationError(
            "Only PricePerMealPlanModel + PricePerMealPlanModel is allowed for PricePerMealPlanModel."
        )

    @property
    def meal_plan_ids(self):
        return self.get("price_per_meal_plan").keys()

    def get_restricted_to_meal_plan_id(self, reference_meal_plan_id):
        return PricePerMealPlanModel(
            price_per_meal_plan={
                meal_plan_id: price
                for meal_plan_id, price in self.get("price_per_meal_plan").iteritems()
                if meal_plan_id == reference_meal_plan_id
            }
        )
