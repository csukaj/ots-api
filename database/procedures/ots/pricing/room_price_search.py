from ots.pricing.abstract_price_search import AbstractPriceSearch


class RoomPriceSearch(AbstractPriceSearch):
    def __init__(self, **keyword_parameters):
        super(RoomPriceSearch, self).__init__(**keyword_parameters)
        self.order_itemable_index = None
        self.room_request = None
        self.device_id = None
        self.device = None
        self.open_date_ranges = None
        self.usage = None

    def find(
        self,
        order_itemable_index,
        room_request,
        device_id,
        combination_wrapper,
        open_date_ranges,
        switch_wrappers=None,
        rule_wrappers=None,
    ):
        # type: (...) -> AbstractOffer
        self.order_itemable_index = order_itemable_index
        self.room_request = room_request
        self.device_id = device_id
        self.combination_wrapper = combination_wrapper
        self.open_date_ranges = open_date_ranges
        self.switch_wrapper = switch_wrappers
        self.rule_wrapper = rule_wrappers
        self._load_device()
        self.params = {
            "subject_type": "App\\Device",
            "subject_id": self.device["id"],
            "productable_type": "App\\Device",
            "productable_id": self.device_id,
            "price_modifiable_type": self.device["deviceable_type"],
            "price_modifiable_id": self.device["deviceable_id"],
            "request": self.room_request,
        }
        return self._get_offers()

    def _load_device(self):
        if self.device_id in self.abstract_search.room_matcher.devices_data:
            self.device = self.abstract_search.room_matcher.devices_data[self.device_id]

    def _get_offers(self):
        if self.device["deviceable_type"] != "App\\Organization":
            return {"prices": None, "usages": None}
        return self._get_abstract_offers(self.params)
