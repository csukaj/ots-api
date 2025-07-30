from ots.pricing.abstract_price_search import AbstractPriceSearch
from stylers.utils import execute_cached_query


class CharterPriceSearch(AbstractPriceSearch):
    SHIP_GROUP_TYPE_TX_ID = 375

    def __init__(self, **keyword_parameters):
        super(CharterPriceSearch, self).__init__(**keyword_parameters)
        self.order_itemable_index = None
        self.charter_request = None
        self.ship_group_id = None
        self.open_date_ranges = None
        self.usage = None
        self.ship_group = None

    def find(
        self,
        order_itemable_index,
        charter_request,
        ship_group_id,
        combination_wrapper,
        open_date_ranges,
        switch_wrapper=None,
        rule_wrapper=None,
    ):
        self.order_itemable_index = order_itemable_index
        self.charter_request = charter_request
        self.ship_group_id = ship_group_id
        self.combination_wrapper = combination_wrapper
        self.open_date_ranges = open_date_ranges
        self.switch_wrapper = switch_wrapper
        self.rule_wrapper = rule_wrapper

        self.ship_group = self._get_ship_group(self.ship_group_id)
        self.params = {
            "subject_type": "App\\ShipGroup",
            "subject_id": self.ship_group_id,
            "productable_type": "App\\ShipGroup",
            "productable_id": self.ship_group_id,
            "price_modifiable_type": "App\\ShipGroup",
            "price_modifiable_id": self.ship_group_id,
            "request": self.charter_request,
        }
        return self._get_offers()

    def _get_ship_group(self, ship_group_id):
        return execute_cached_query(
            self.plpy,
            """
                SELECT * FROM organization_groups
                WHERE id = {id}
                AND type_taxonomy_id = {type}
                AND deleted_at IS NULL
                {only_active}
        """.format(
                id=ship_group_id,
                type=self.SHIP_GROUP_TYPE_TX_ID,
                only_active="AND is_active" if not self.abstract_search.show_inactive else "",
            ),
        )[0]

    def _get_offers(self):
        return self._get_abstract_offers(self.params)
