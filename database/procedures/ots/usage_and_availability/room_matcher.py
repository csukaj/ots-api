from ots.common.config import Config
from ots.usage_and_availability.abstract_matcher import AbstractMatcher
from stylers.utils import execute_cached_query


class RoomMatcher(AbstractMatcher):
    """
    Search for available devices for request
    """

    MORPH_TYPE = "App\\Organization"

    def __init__(self, **keyword_parameters):
        super(RoomMatcher, self).__init__(**keyword_parameters)

        self.organization_id = keyword_parameters["organization_id"]

        self.devices = None
        self.device_usages = None
        self.available_devices = None
        self.available_device_count = None
        self.minimum_nights = None
        self.device_child_bed_policies = None

    def check(self):
        """
        Class endpoint
        Returns with the matching devices for search request
        """
        if not self.use_default_availability:
            self.date_ranges = self._get_date_ranges(self.organization_id)
            if not self._is_in_open_date_range_with_matching_minimum_nights(
                self.organization_id, self.date_ranges
            ) or self._is_in_closed_date_range(self.date_ranges):
                return None

        (self.devices, self.device_usages) = self._get_devices(self.organization_id)
        if not self.use_default_availability:
            self.minimum_nights = self._get_device_minimum_nights()

        (self.available_devices, self.available_device_count) = self._get_availabilities_for_rooms(
            self.devices, self.minimum_nights
        )
        self._load_device_child_bed_policies()
        return self._get_available_devices(self.available_devices, self.available_device_count)

    def _get_availabilities_for_rooms(self, devices, minimum_nights):
        """
        Get device availabilities by search interval or default availabilities
        """
        available_devices = []
        for device in devices:
            availabilities = self._get_availability_of_a_device(device, minimum_nights)
            if availabilities:
                available_devices.append(availabilities)

        available_device_count = 0
        for device in available_devices:
            available_device_count += device["available"]

        return available_devices, available_device_count

    def _is_child_bed_policy_strict(self, available_device):

        return self.device_child_bed_policies[available_device["device_id"]]

    def _load_device_child_bed_policies(self):
        strict_policies = {}
        if len(self.devices) == 0:
            return

        rows = execute_cached_query(
            self.plpy,
            """
                    SELECT id
                    FROM organization_classifications
                    WHERE
                        organization_id = {organization_id}
                        AND classification_taxonomy_id = {strict_policy_taxonomy_id}
                        AND value_taxonomy_id = {enabled_value_taxonomy_id}
                        AND deleted_at IS NULL
                    LIMIT 1
                """.format(
                organization_id=self.organization_id,
                strict_policy_taxonomy_id=Config.ORGANIZATION_STRICT_CHILD_BED_POLICY_TAXONOMY_ID,
                enabled_value_taxonomy_id=Config.ORGANIZATION_STRICT_CHILD_BED_POLICY_ENABLED_TAXONOMY_ID,
            ),
        )
        row_count = len(rows)

        for device_id in self.devices:
            strict_policies[device_id] = row_count > 0

        rows = execute_cached_query(
            self.plpy,
            """
                            SELECT device_id, value_taxonomy_id
                            FROM device_classifications
                            WHERE
                                device_id IN ({device_ids})
                                AND classification_taxonomy_id = {strict_policy_taxonomy_id}
                                AND deleted_at IS NULL
                        """.format(
                device_ids=",".join(str(d) for d in self.devices),
                strict_policy_taxonomy_id=Config.DEVICE_STRICT_CHILD_BED_POLICY_TAXONOMY_ID,
            ),
        )

        for row in rows:
            strict_policies[row["device_id"]] = (
                row["value_taxonomy_id"] == Config.DEVICE_STRICT_CHILD_BED_POLICY_ENABLED_TAXONOMY_ID
            )

        self.device_child_bed_policies = strict_policies
