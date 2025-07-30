from ots.usage_and_availability.abstract_matcher import AbstractMatcher


class CabinMatcher(AbstractMatcher):
    """
    Search for available devices for request
    """

    MORPH_TYPE = "App\\Cruise"

    def __init__(self, **keyword_parameters):
        super(CabinMatcher, self).__init__(**keyword_parameters)

        self.organization_id = keyword_parameters["organization_id"]
        self.cruise_id = keyword_parameters["cruise_id"]

        self.devices = None
        self.device_usages = None
        self.available_devices = None
        self.available_device_count = None
        self.minimum_nights = None

    def check(self):
        """
        Class endpoint
        Returns with the matching devices for search request
        """
        if not self.use_default_availability:
            self.date_ranges = self._get_date_ranges(self.cruise_id)

            if not self._is_in_open_date_range_with_matching_minimum_nights(
                self.organization_id, self.date_ranges
            ) or self._is_in_closed_date_range(self.date_ranges):
                return None

        (self.devices, self.device_usages) = self._get_devices(self.cruise_id)

        if not self.use_default_availability:
            self.minimum_nights = self._get_device_minimum_nights()

        (self.available_devices, self.available_device_count) = self._get_availabilities_for_cabins(
            self.devices, self.minimum_nights
        )

        return self._get_available_devices(self.available_devices, self.available_device_count)

    def _get_availabilities_for_cabins(self, devices, minimum_nights):
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
        return False
