from ots.repository.utils.object_mapper import Column, BaseModel
from datetime import datetime
from stylers.date_helpers import datetimestr_to_datetime


class PriceElementModel(BaseModel):
    margin_type_taxonomy_id = Column(int)
    created_at = Column(datetime, converter=datetimestr_to_datetime)
    meal_plan_id = Column(str)  # int would break a lot of things
    date_range_id = Column(int)
    rack_price = Column(float)
    net_price = Column(float)
    updated_at = Column(datetime, converter=datetimestr_to_datetime)
    model_meal_plan_id = Column(int)
    price_id = Column(int)
    deleted_at = Column(datetime, converter=datetimestr_to_datetime)
    margin_value = Column(float)
    id = Column(int)
