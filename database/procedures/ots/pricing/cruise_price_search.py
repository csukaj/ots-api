from ots.pricing.abstract_price_search import AbstractPriceSearch
from stylers.utils import execute_cached_query


class CruisePriceSearch(AbstractPriceSearch):
    def __init__(self, **keyword_parameters):
        super(CruisePriceSearch, self).__init__(**keyword_parameters)
        self.cruise_id = keyword_parameters["cruise_id"]

        self.order_itemable_index = None
        self.room_request = None
        self.device_id = None
        self.open_date_ranges = None
        self.usage = None

    def find(
        self,
        order_itemable_index,
        room_request,
        device_id,
        combination_wrapper,
        open_date_ranges,
        switch_wrapper=None,
        rule_wrapper=None,
    ):
        self.order_itemable_index = order_itemable_index
        self.room_request = room_request
        self.device_id = device_id
        self.combination_wrapper = combination_wrapper
        self.open_date_ranges = open_date_ranges
        self.switch_wrapper = switch_wrapper
        self.rule_wrapper = rule_wrapper
        self._load_device()
        self.params = {
            "subject_type": "App\\Device",
            "subject_id": self.device["id"],
            "productable_type": "App\\CruiseDevice",
            "productable_id": self.cruise_device["id"],
            "price_modifiable_type": "App\\Cruise",
            "price_modifiable_id": self.cruise_id,
            "request": self.room_request,
        }
        return self._get_offers()

    def _load_device(self):
        self.device = execute_cached_query(
            self.plpy,
            """SELECT * FROM "devices" WHERE "id" = {id} AND deleted_at IS NULL""".format(
                id=str(self.device_id)
            ),
        )[0]
        self.cruise_device = execute_cached_query(
            self.plpy,
            """SELECT * FROM "cruise_devices" WHERE "cruise_id" = {cruise_id} AND "device_id" = {device_id}""".format(
                cruise_id=str(self.cruise_id), device_id=str(self.device_id)
            ),
        )[0]

    def _get_offers(self):
        return self._get_abstract_offers(self.params)
