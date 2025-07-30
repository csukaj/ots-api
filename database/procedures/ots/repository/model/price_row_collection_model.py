from ots.repository.model.price import Price
from ots.repository.utils.object_mapper import Column, BaseModel, HasMap
from ots.repository.model.price_row_model import PriceRowModel
from copy import deepcopy
from ots.repository.exceptions.unsupported_operation_error import UnsupportedOperationError
from ots.repository.model.price_per_meal_plan_model import PricePerMealPlanModel


class PriceRowCollectionModel(BaseModel):
    price_rows = HasMap(str, PriceRowModel)

    def __add__(self, other):
        if isinstance(other, PriceRowCollectionModel):
            return PriceRowCollectionModel(
                price_rows=PriceRowCollectionModel._merge_price_rows(self.price_rows, other.price_rows)
            )
        raise UnsupportedOperationError(
            "Only PriceRowCollectionModel + PriceRowCollectionModel is allowed for PriceRowCollectionModel."
        )

    def __sub__(self, other):
        if isinstance(other, PriceRowCollectionModel):
            opposite_price_row_collection = -other
            return PriceRowCollectionModel(
                price_rows=PriceRowCollectionModel._merge_price_rows(
                    self.price_rows, opposite_price_row_collection.price_rows
                )
            )
        raise UnsupportedOperationError(
            "Only PriceRowCollectionModel - PriceRowCollectionModel is allowed for PriceRowCollectionModel."
        )

    def __mul__(self, other):
        if isinstance(other, (float, int)):
            new_price_rows = {}
            for price_row_name, price_row in self.get("price_rows").iteritems():
                new_price_rows[price_row_name] = PriceRowModel(
                    meta=price_row.get("meta"),
                    amount=price_row.get("amount"),
                    price=price_row.get("price") * other,
                )
            return PriceRowCollectionModel(price_rows=new_price_rows)
        raise UnsupportedOperationError(
            "Only PriceRowCollectionModel * constant is allowed for PriceRowCollectionModel."
        )

    def __rmul__(self, other):
        return self.__mul__(other)

    def __neg__(self):
        return self.__mul__(-1)

    @classmethod
    def _merge_price_rows(cls, price_rows_a, price_rows_b):
        summary = {}
        a_price_row_keys = set(price_rows_a.keys())
        b_price_row_keys = set(price_rows_b.keys())
        keys = a_price_row_keys.union(b_price_row_keys)
        for key in keys:
            a = price_rows_a.get(key, PriceRowModel(meta=None, amount=1, price=Price(net=0, rack=0)))
            b = price_rows_b.get(key, PriceRowModel(meta=None, amount=1, price=Price(net=0, rack=0)))
            if a.meta is None and b.meta is None:
                # we allow price_rows to be merged even if none of them has valid meta
                # this is because the offers can make new price_rows without meta and amount
                summary[key] = b + a.price
            elif a.meta is None:
                summary[key] = b + a.price
            else:
                summary[key] = a + b.price

        return summary

    @property
    def summary(self):
        # type: () -> Price
        return sum((i.summary for _, i in self.get("price_rows").iteritems()), Price(net=0, rack=0))

    def get_without_negative_price_rows(self):

        original_price_rows = {
            price_row_name: PriceRowModel(
                meta=i.meta, amount=i.amount, price=max(i.price, Price(net=0, rack=0))
            )
            for price_row_name, i in self.get("price_rows").iteritems()
            if i.get("meta") is not None
        }
        # there are some offers where we don't exactly know which price_row will be the deduction base
        # those offers will be excluded from this operation
        non_original_price_rows = {
            price_row_name: PriceRowModel(meta=i.meta, amount=i.amount, price=Price(net=0, rack=0))
            for price_row_name, i in self.get("price_rows").items()
            if i.get("meta") is None
        }
        original_price_rows.update(non_original_price_rows)
        return PriceRowCollectionModel(price_rows=original_price_rows)

