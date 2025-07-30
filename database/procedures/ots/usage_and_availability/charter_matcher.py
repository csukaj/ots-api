from math import ceil

from ots.common.age_resolver import AgeResolver
from ots.usage_and_availability.abstract_matcher import AbstractMatcher


class CharterMatcher(AbstractMatcher):
    MORPH_TYPE = "App\\ShipGroup"

    def __init__(self, **keyword_parameters):
        super(CharterMatcher, self).__init__(**keyword_parameters)

        self.organization_group_id = keyword_parameters["organization_group_id"]

    def check(self):
        """
        Class endpoint
        Returns with the matching ship groups for search request
        """
        ship_group = self._get_ship_group()

        # if date range or minimum nights does not fit
        if not self.use_default_availability and not self.show_inactive:
            date_ranges = self._get_date_ranges(ship_group["id"])
            if not self._is_in_open_date_range_with_matching_minimum_nights(
                ship_group["id"], date_ranges
            ) or self._is_in_closed_date_range(date_ranges):
                return None
        # always get ship group if there's no request
        if len(self.request_handler.request) == 0:
            return ship_group

        age_resolver = AgeResolver(self.plpy, CharterMatcher.MORPH_TYPE, ship_group["id"])
        ship_usages = self._get_ship_usages(ship_group["id"])
        ship_group_available_usages = {
            k: v * int(ship_group["ship_count"]) for k, v in ship_usages.iteritems()
        }
        request_usages = self.request_handler.request[0]["usage"]

        if self._has_banned_age_in_request(request_usages, age_resolver):
            return None

        required_ship_count_for_usage = self._get_ship_count_for_usage(
            ship_usages, age_resolver.resolve_room_usage(request_usages)
        )

        # if availability does not fit -- the whole ship need to be empty
        devices = self._get_ship_devices(ship_group["id"])
        fulfilled = True
        for device in devices:
            availability = self._get_availability_of_a_device(device["id"], {})
            if (
                availability is None
                or availability["available"] < device["amount"] * required_ship_count_for_usage
            ):
                fulfilled = False
        if not fulfilled:
            return None

        ship_group["is_overbooked"] = False
        ship_group["required_ship_count"] = required_ship_count_for_usage
        return ship_group

    def _get_ship_group(self):
        sql = """
            SELECT
                organization_groups.id,
                organization_groups.name_description_id,
                count(organizations.id)::integer AS ship_count
            FROM organization_groups
            JOIN organizations
                ON organizations.parentable_id = organization_groups.id
                    AND organizations.parentable_type = 'App\\ShipGroup'
                    AND organizations.type_taxonomy_id = {organization_type}
                    AND organizations.deleted_at IS NULL
                    {only_active_organizations}
            WHERE
                organization_groups.id = {organization_group_id}
                AND organization_groups.deleted_at IS NULL
                {only_active_organization_groups}
            GROUP BY organization_groups.id, organization_groups.name_description_id
        """
        return self.plpy.execute(
            sql.format(
                organization_group_id=str(self.organization_group_id),
                organization_type="13",
                only_active_organizations="AND organizations.is_active" if not self.show_inactive else "",
                only_active_organization_groups="AND organization_groups.is_active"
                if not self.show_inactive
                else "",
            )
        )[0]

    def _get_ship_usages(self, organization_group_id):
        sql = """
            SELECT * FROM view_ships
            WHERE organization_group_id = {organization_group_id}
        """
        rows = self.plpy.execute(sql.format(organization_group_id=str(organization_group_id)))
        usages = {}
        for row in rows:
            usages.setdefault(row["name"], 0)
            usages[row["name"]] += int(row["amount"])
        return usages

    def _get_ship_devices(self, organization_group_id):
        sql = """
            SELECT * FROM devices
            WHERE deviceable_type='App\\ShipGroup' 
            AND deviceable_id = {deviceable_id}
            AND devices.deleted_at IS NULL
        """
        return self.plpy.execute(sql.format(deviceable_id=str(organization_group_id)))

    def _is_child_bed_policy_strict(self, available_device):
        return False

    @staticmethod
    def _get_ship_count_for_usage(ship_usages, request_usages):
        min_ship_count = 0.0
        for age_range, amount in request_usages.iteritems():
            min_ship_count = max(min_ship_count, float(amount) / float(ship_usages[age_range]))
        return ceil(min_ship_count)
