from stylers.date_helpers import datestr_to_datetime


class AbstractPriceModifier(object):
    def __init__(self, **keyword_parameters):
        self.keyword_parameters = keyword_parameters  # for copy
        self.plpy = keyword_parameters["plpy"]
        self.request = keyword_parameters["request"]
        self.request_from_time = keyword_parameters["request_from_time"]
        self.request_to_time = keyword_parameters["request_to_time"]
        self.date_ranges = keyword_parameters["date_ranges"]
        self.price_modifiable_type = keyword_parameters["price_modifiable_type"]
        self.price_modifiable_id = keyword_parameters["price_modifiable_id"]
        self.available_devices = keyword_parameters["available_devices"]
        self.combination_from_time = keyword_parameters.get("combination_from_time")
        self.combination_to_time = keyword_parameters.get("combination_to_time")
        self.deduction_base_prices_of_switch = keyword_parameters.get("deduction_base_prices_of_switch")
        self.abstract_search = None
        self.offer = None

    def get_id(self):
        pass

    def get_name(self):
        pass

    def get_priority(self):
        pass

    def get_application_type(self):
        pass

    def get_modifier_type(self):
        pass

    def get_offer(self):
        pass

    def get_applicable_devices(self, override_minimum_nights=False):
        pass

    def get_description(self):
        pass

    def _get_taxonomy_name(self, taxonomy_id):
        return None if taxonomy_id is None else self.abstract_search.taxonomies[str(taxonomy_id)]

    @staticmethod
    def get_all_combinations(plpy, price_modifier_ids):
        pass

    def is_valid_free_night_price_modifier(self):
        if self.offer is None:
            return False

        return (
            self.get_offer() == "free_nights"
            and self.offer is not None
            and "discounted_nights" in self.offer.meta
            and "cumulation_frequency" in self.offer.meta
            and self.get_applicable_devices(True)
        )
