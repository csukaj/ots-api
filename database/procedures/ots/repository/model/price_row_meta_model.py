from ots.repository.utils.object_mapper import Column, BaseModel


class PriceRowMetaModel(BaseModel):
    """
    The object Contains information about the price row
    """

    id = Column(int)
    price_name = Column(str)
    age_range = Column(str)
    amount = Column(int)
    extra = Column(bool)  # if this is an extra price
    mandatory = Column(bool)  # if this is a mandatory price

    # about the product
    productable_id = Column(int)
    product_id = Column(int)
    product_type_taxonomy_id = Column(int)

    # about the date_ranges
    non_empty_date_ranges = Column(list)  # list of integers

    def isExtra(self):
        return self.extra

    def isMandatory(self):
        return self.mandatory
