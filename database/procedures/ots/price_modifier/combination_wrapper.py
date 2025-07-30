from ots.price_modifier.price_modifier import PriceModifier
from ots.price_modifier.wrapper import Wrapper


class CombinationWrapper(Wrapper):
    """
    Searches and create combinations
    """

    def __init__(self, **keyword_parameters):
        super(CombinationWrapper, self).__init__(**keyword_parameters)

    @classmethod
    def _get_combinable_price_modifier_ids(cls, plpy, price_modifier_ids):
        return PriceModifier.get_all_combinations(plpy, price_modifier_ids)
