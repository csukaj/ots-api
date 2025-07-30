from ots.repository.model.price import Price
from ots.repository.utils.object_mapper import Column, BaseModel
from ots.repository.model.price_row_meta_model import PriceRowMetaModel
from ots.repository.model.price_per_meal_plan_model import PricePerMealPlanModel
from ots.repository.exceptions.unsupported_operation_error import UnsupportedOperationError


def init_price(net, rack):
    return Price(net=net, rack=rack)


class PriceRowModel(BaseModel):
    meta = Column(PriceRowMetaModel, original_field_name="price")
    amount = Column(int)  # the number of usages in this price row
    price = Column(Price, converter=init_price, original_field_names=["net", "rack"])

    def __add__(self, other):
        if isinstance(other, Price):
            return PriceRowModel(meta=self.meta, amount=self.amount, price=self.price + other)
        else:
            raise UnsupportedOperationError("Only PriceRowModel + Price is allowed for PriceRowModel")


    @property
    def summary(self):
        # type: () -> Price
        return self.get('price') * self.get('amount')