import json
from copy import deepcopy
from datetime import date, datetime, timedelta

from ots.common.date_range_cooker import DateRangeCooker
from stylers.date_helpers import datetimestr_to_datetime
from stylers.utils import execute_cached_query


class AbstractMatcher(object):
    """
    Search for available devices for request
    """

    MORPH_TYPE = None
    OPEN_DATE_RANGE_TX_ID = 62
    CLOSED_DATE_RANGE_TX_ID = 63

    def __init__(self, **keyword_parameters):
        """
        Initialize object
        """
        self.plpy = keyword_parameters["plpy"] if "plpy" in keyword_parameters else plpy
        self.request_handler = keyword_parameters["request_handler"]
        self.cart_summary = keyword_parameters.get("cart_summary")
        self.show_inactive = keyword_parameters["show_inactive"]

        self.from_date = None
        self.to_date = None
        self.date_ranges = None
        self.minimum_nights = []
        self.use_default_availability = None
        self.devices_data = {}

    def set_interval(self, from_date, to_date):
        if from_date is None or to_date is None:
            self.use_default_availability = True
            self.from_date = None
            self.to_date = None
        else:
            self.use_default_availability = False
            self.from_date = from_date
            self.to_date = to_date

    def _get_date_ranges(self, date_rangeable_id):
        sql = """
            SELECT * FROM date_ranges
            WHERE
                date_ranges.date_rangeable_type = '{date_rangeable_type}'
                AND date_ranges.date_rangeable_id = {date_rangeable_id}
                AND deleted_at IS NULL
                AND (from_time, to_time + INTERVAL '1 day') OVERLAPS (DATE '{from_date}', DATE '{to_date}')
                AND type_taxonomy_id IN ({taxonomy_ids})
        """
        sql = sql.format(
            date_rangeable_type=self.MORPH_TYPE,
            date_rangeable_id=str(date_rangeable_id),
            from_date=self.from_date,
            to_date=self.to_date,
            taxonomy_ids=",".join(str(x) for x in [self.OPEN_DATE_RANGE_TX_ID, self.CLOSED_DATE_RANGE_TX_ID]),
        )
        ranges = execute_cached_query(self.plpy, sql)

        date_ranges = {self.OPEN_DATE_RANGE_TX_ID: [], self.CLOSED_DATE_RANGE_TX_ID: []}
        for range_data in ranges:
            date_ranges[range_data["type_taxonomy_id"]].append(range_data)

        return date_ranges

    def _is_in_open_date_range_with_matching_minimum_nights(self, date_rangeable_id, date_ranges):
        cooker = DateRangeCooker(
            plpy=self.plpy,
            date_rangeable_type=self.MORPH_TYPE,
            date_rangeable_id=date_rangeable_id,
            from_time=datetime.strptime(self.from_date, "%Y-%m-%d"),
            to_time=datetime.strptime(self.to_date, "%Y-%m-%d"),
            date_ranges=date_ranges,
        )
        return cooker.is_covered()

    def _is_in_closed_date_range(self, date_ranges):
        return len(date_ranges[self.CLOSED_DATE_RANGE_TX_ID]) > 0

    def has_common_meal_plan(self, date_ranges, meal_planable_type, meal_planable_id):
        common_meal_plans = None
        model_meal_plans = self._get_model_meal_plans(date_ranges, meal_planable_type, meal_planable_id)
        for mp_range in model_meal_plans:
            if common_meal_plans is None:
                common_meal_plans = set(mp_range["meal_plans"])
            else:
                common_meal_plans = common_meal_plans.intersection(set(mp_range["meal_plans"]))

        return common_meal_plans is not None and len(common_meal_plans) > 0

    def _get_model_meal_plans(self, date_ranges, meal_planable_type, meal_planable_id):
        if not date_ranges[self.OPEN_DATE_RANGE_TX_ID]:
            return

        return execute_cached_query(
            self.plpy,
            """
            SELECT date_range_id, array_agg(meal_plan_id) as meal_plans
            FROM model_meal_plans
            WHERE
                meal_planable_type = '{meal_planable_type}'
                AND meal_planable_id = {meal_planable_id}
                AND deleted_at IS NULL
                AND date_range_id IN ({date_range_ids})
            GROUP BY date_range_id
        """.format(
                meal_planable_type=meal_planable_type,
                meal_planable_id=str(meal_planable_id),
                date_range_ids=",".join(str(x["id"]) for x in date_ranges[self.OPEN_DATE_RANGE_TX_ID]),
            ),
        )

    @staticmethod
    def _in_age_range(age, age_range, strict=False):
        """
        Check if age range matches to given age (less or equal)
        """
        if strict:
            return (
                age_range["from_age"] <= age <= age_range["to_age"]
                or age_range["from_age"] < age
                and age_range["to_age"] is None
            )
        else:
            return age <= age_range["to_age"] or age_range["to_age"] is None

    @staticmethod
    def _has_banned_age_in_request(request_usages, age_resolver):
        request_age_ranges = age_resolver.resolve_room_usage(request_usages).keys()
        named_age_ranges = age_resolver.get_age_ranges_dict()
        for request_age_range in request_age_ranges:
            if named_age_ranges[request_age_range]["banned"]:
                return True
        return False

    @staticmethod
    def _are_ranges_mergeable(first, second):
        first_to_time = datetimestr_to_datetime(first["to_time"])
        second_from_time = datetimestr_to_datetime(second["from_time"])
        return first_to_time + timedelta(seconds=1) == second_from_time

    def _get_devices(self, deviceable_id):
        """
        Get devices and usages
        """
        rows = []
        if self.MORPH_TYPE == "App\\Organization":
            rows = execute_cached_query(
                self.plpy,
                "SELECT * FROM view_accommodation_rooms WHERE organization_id = {deviceable_id}".format(
                    deviceable_id=str(deviceable_id)
                ),
            )
        elif self.MORPH_TYPE == "App\\ShipGroup":
            rows = execute_cached_query(
                self.plpy,
                "SELECT * FROM view_ship_cabins WHERE organization_group_id = {deviceable_id}".format(
                    deviceable_id=str(deviceable_id)
                ),
            )
        elif self.MORPH_TYPE == "App\\Cruise":
            rows = execute_cached_query(
                self.plpy,
                """
                SELECT * FROM view_ship_cabins WHERE organization_group_id = (
                    SELECT ship_group_id FROM cruises WHERE id = {cruise_id}
                )
                """.format(
                    cruise_id=deviceable_id
                ),
            )

        last_device = None
        last_usage_id = None
        device_ids = []
        device_usages = {}
        usages = {}
        usage = {}

        for row in rows:
            if last_device != row["device_id"]:
                device_ids.append(row["device_id"])

            if last_device != row["device_id"] and last_device is not None:
                usages = {}

            if row["device_usage_id"] != last_usage_id and last_usage_id is not None:
                usage = {}

            usage[row["name"]] = row["amount"]
            last_device = row["device_id"]
            last_usage_id = row["device_usage_id"]
            usages.update({last_usage_id: usage})
            device_usages.update({last_device: usages})
            if row["device_id"] not in self.devices_data:
                self.devices_data[row["device_id"]] = (
                    json.loads(row["device"]) if type(row["device"]) is str else row["device"]
                )

        return device_ids, device_usages

    def _get_availability_of_a_device(self, device_id, minimum_nights):
        if not self.use_default_availability and not self.show_inactive:
            if minimum_nights.get(device_id) and not self._device_minimum_nights_matches(device_id):
                return None

            result = execute_cached_query(
                self.plpy,
                """
                SELECT DATE(MIN(from_time)) AS min_date FROM availabilities
                WHERE available_type = 'App\\Device' AND available_id = {available_id}
            """.format(
                    available_id=str(device_id)
                ),
            )

            min_date = result[0]["min_date"]
            if type(min_date) is date:
                min_date = min_date.strftime("%Y-%m-%d")
            if min_date > self.from_date:  # compares str to str
                return None

            sql = """
                SELECT MIN(amount) AS max_av FROM (
                        SELECT * FROM availabilities
                        WHERE available_type = 'App\\Device' AND available_id = {available_id}
                        AND from_time <= '{from_date} 12:00:00'
                        AND (to_time >= '{to_date} 12:00:00' OR to_time is NULL)
                    UNION
                        SELECT * FROM availabilities
                        WHERE available_type = 'App\\Device' AND available_id = {available_id}
                        AND from_time >= '{from_date} 12:00:00'
                        AND from_time < '{to_date} 12:00:00'
                    UNION
                        SELECT * FROM availabilities
                        WHERE available_type = 'App\\Device' AND available_id = {available_id}
                        AND to_time > '{from_date} 12:00:00'
                        AND to_time <= '{to_date} 12:00:00'
                ) temp
            """.format(
                available_id=str(device_id), from_date=self.from_date, to_date=self.to_date
            )
        else:
            sql = """
                SELECT amount as max_av FROM availabilities
                WHERE available_type = 'App\\Device' AND available_id = {available_id}
                AND to_time is NULL
            """.format(
                available_id=str(device_id)
            )

        result = execute_cached_query(self.plpy, sql)

        if result[0]["max_av"] > 0:
            return {"device_id": device_id, "available": result[0]["max_av"]}

        return None

    def _get_available_devices(self, available_devices, available_device_count):
        """
        Check if organization has possibility to accept request usage
        """
        if available_device_count < len(self.request_handler.request):
            return None

        temp_request = deepcopy(self.request_handler.request)
        for request in temp_request:
            request["fulfilled"] = False

        temp_available_devices = deepcopy(available_devices)
        index = 0
        device_union = {}

        for available_device in temp_available_devices:
            available_devices[index]["usage_pairs"] = set([])
            i = 0
            strict = self._is_child_bed_policy_strict(available_device)
            for request in temp_request:
                matching_usage_ids = self._get_matching_usage_ids(
                    self.device_usages[available_device["device_id"]], request["usage"], strict
                )
                if available_device["available"] > 0 and matching_usage_ids:
                    request["fulfilled"] = True
                    if available_device["device_id"] not in device_union:
                        device_union[available_device["device_id"]] = int(available_device["available"])
                    available_devices[index]["usage_pairs"].add(i)
                i += 1
            index += 1

        for request in temp_request:
            if not request["fulfilled"]:
                return None

        available_device_count = 0
        for key, device_count in device_union.iteritems():
            available_device_count += device_count

        if available_device_count < len(temp_request):
            return None

        filtered_devices = []
        for available_device in filter(lambda x: len(x["usage_pairs"]) > 0, available_devices):
            available_device["usage_pairs"] = list(available_device["usage_pairs"])
            filtered_devices.append(available_device)

        filtered_devices = self._mark_overbooked_devices(filtered_devices)

        return filtered_devices

    def _mark_overbooked_devices(self, available_devices):
        counts = self._get_device_counts_in_cart()
        for available_device in available_devices:
            available_device["is_overbooked"] = available_device["device_id"] in counts and (
                counts[available_device["device_id"]] > available_device["available"]
            )
        return available_devices

    def _get_device_counts_in_cart(self):
        if self.cart_summary is None:
            return {}

        counts = {}
        for cart_element in self.cart_summary["elements"]:
            if cart_element["order_itemable_type"] not in ["App\\Device", "App\\ShipGroup"]:
                continue
            counts[cart_element["order_itemable_id"]] = counts.get(cart_element["order_itemable_id"], 0) + 1
        return counts

    def _get_matching_usage_ids(self, device_usages, request_usages, strict):
        """
        Check if device is matching to request
        """
        return [
            usage_id
            for usage_id in device_usages
            if self._is_matching_usage(
                device_usages[usage_id], request_usages, self.request_handler.age_resolver, strict
            )
        ]

    def _is_matching_usage(self, device_usage, request_usages, age_resolver, strict):
        if self._has_banned_age_in_request(request_usages, age_resolver):
            return False

        temp_request_usages = deepcopy(request_usages)
        temp_device_usage = deepcopy(device_usage)
        named_age_ranges = age_resolver.get_age_ranges_dict()

        for request_usage_item in temp_request_usages:
            request_usage_item["fulfilled"] = False

        for age_range_name, amount in temp_device_usage.iteritems():
            for request_usage_item in temp_request_usages:
                if (
                    not request_usage_item["fulfilled"]
                    and self._in_age_range(
                        request_usage_item["age"], named_age_ranges[age_range_name], strict
                    )
                    and amount >= request_usage_item["amount"]
                ):
                    request_usage_item["fulfilled"] = True
                    amount = amount - request_usage_item["amount"]

        fulfilled = True
        for request_usage_item in temp_request_usages:
            if not request_usage_item["fulfilled"]:
                fulfilled = False

        return fulfilled

    def _device_minimum_nights_matches(self, device):
        min_nights_list = self.minimum_nights[device]
        from_time = datetime.strptime(self.from_date, "%Y-%m-%d")
        to_time = datetime.strptime(self.to_date, "%Y-%m-%d")
        holiday_nights = (to_time - from_time).days

        for date_range in self.date_ranges[self.OPEN_DATE_RANGE_TX_ID]:
            if date_range["id"] in min_nights_list:
                minimum_nights = min_nights_list[date_range["id"]]
                if minimum_nights > holiday_nights:
                    return False
        return True

    def _get_device_minimum_nights(self):
        minimum_nights = {}
        if not self.devices:
            return

        rows = execute_cached_query(
            self.plpy,
            """
            SELECT *
            FROM device_minimum_nights
            WHERE
                device_id IN ("""
            + ",".join(str(x) for x in self.devices)
            + """)
                AND date_range_id IN ("""
            + ",".join(str(x["id"]) for x in self.date_ranges[self.OPEN_DATE_RANGE_TX_ID])
            + """)
                AND deleted_at IS NULL
        """,
        )

        for item in rows:
            if item["device_id"] not in minimum_nights:
                minimum_nights[item["device_id"]] = {}
            minimum_nights[item["device_id"]][item["date_range_id"]] = item["minimum_nights"]

        return minimum_nights

    def _is_child_bed_policy_strict(self, available_device):
        pass
