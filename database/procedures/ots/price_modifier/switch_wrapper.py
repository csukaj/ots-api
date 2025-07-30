from ots.price_modifier.wrapper import Wrapper


class SwitchWrapper(Wrapper):
    """
    Searches and create combinations
    """

    def __init__(self, **keyword_parameters):
        super(SwitchWrapper, self).__init__(**keyword_parameters)

    @classmethod
    def _get_combinable_price_modifier_ids(cls, plpy, price_modifier_ids):
        combinable_ids = []
        for first_price_modifier_id in price_modifier_ids:
            for second_price_modifier_id in price_modifier_ids:
                if first_price_modifier_id < second_price_modifier_id:
                    combinable_ids.append([first_price_modifier_id, second_price_modifier_id])
        return combinable_ids
