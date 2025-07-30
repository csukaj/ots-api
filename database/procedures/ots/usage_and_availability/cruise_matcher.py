from datetime import datetime, timedelta
from ots.usage_and_availability.abstract_matcher import AbstractMatcher
from stylers.date_helpers import datestr_to_datetime


class CruiseMatcher(AbstractMatcher):
    """
    Search for available cruises for request
    """

    SCH_FREQ_ONCE_TX = 435
    SCH_FREQ_WEEKLY_TX = 436
    EMBARKATION_TYPE_NONE_TX = 438
    EMBARKATION_TYPE_FINANCIAL_TX = 439
    EMBARKATION_TYPE_TECHNICAL_TX = 440
    EMBARKATION_DIRECTION_NONE_TX = 442
    EMBARKATION_DIRECTION_EMBARK_TX = 443
    EMBARKATION_DIRECTION_DISEMBARK_TX = 444
    EMBARKATION_DIRECTION_2_WAY_TX = 445

    def __init__(self, **keyword_parameters):
        super(CruiseMatcher, self).__init__(**keyword_parameters)

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
        Returns with the matching cruises for search request
        """
        schedule_length_days = self._get_itinerary_length()

        # search without dates
        if self.use_default_availability:  # and not self.show_inactive:
            return [
                {
                    "id": None,
                    "dates": [],
                    "embarkation_time": None,
                    "technical_length_days": schedule_length_days,
                    "financial_length_days": schedule_length_days,
                    "financial_offset_days": 0,
                }
            ]

        # search with dates
        schedules = self._get_matching_schedules()
        if not schedules:
            return []

        matching_schedules = []

        for schedule in schedules:
            start_dates = self._get_matching_start_dates(schedule_length_days, schedule)
            embarkation_time = {
                "time": str(schedule["time"]),
                "time_of_day_taxonomy_id": schedule["time_of_day_taxonomy_id"],
                "precision_taxonomy_id": schedule["precision_taxonomy_id"],
            }
            length_days = schedule_length_days
            financial_length_days = schedule_length_days
            financial_offset_days = 0

            if not start_dates:
                [
                    start_dates,
                    embarkation_time,
                    length_days,
                    financial_length_days,
                    financial_offset_days,
                ] = self._get_matching_partial_itinerary(schedule_length_days, schedule)

            if start_dates:
                matching_schedules.append(
                    {
                        "id": schedule["id"],
                        "dates": start_dates,
                        "embarkation_time": embarkation_time,
                        "technical_length_days": length_days,
                        "financial_length_days": financial_length_days,
                        "financial_offset_days": financial_offset_days,
                    }
                )

        return matching_schedules

    def _get_matching_start_dates(self, length_days, schedule):
        search_from = max([datestr_to_datetime(schedule["from_time"]), datestr_to_datetime(self.from_date)])
        search_to = min(
            [datestr_to_datetime(schedule["to_time"]), datestr_to_datetime(self.to_date)]
        ) + timedelta(hours=23, minutes=59, seconds=59)
        return self._get_start_dates(search_from, search_to, length_days, schedule)

    def _get_start_dates(self, from_time, to_time, length_days, schedule):
        day = from_time
        start_dates = []

        while day + timedelta(days=length_days) - timedelta(seconds=1) <= to_time:
            if day.isoweekday() == schedule["day"]:
                start_dates.append(str(day)[0:10])
                if schedule["frequency_taxonomy_id"] == self.SCH_FREQ_ONCE_TX:
                    return start_dates
            day += timedelta(days=1)

        return start_dates

    def _get_matching_partial_itinerary(self, max_length_days, schedule):
        from_time = datetime.strptime(self.from_date, "%Y-%m-%d")
        to_time = datetime.strptime(self.to_date, "%Y-%m-%d")
        activities = self._get_activities()
        itinerary_start_dates = [
            datetime.strptime(start_date, "%Y-%m-%d")
            for start_date in self._get_itinerary_start_dates_for_partial_itinerary(max_length_days, schedule)
        ]
        options = []

        for itinerary_start_date in itinerary_start_dates:
            embarkation_date = itinerary_start_date if itinerary_start_date >= from_time else None
            embarkation_day = 1
            embarkation_relative_time = {
                "time": str(schedule["time"]) if schedule["time"] is not None else None,
                "time_of_day_taxonomy_id": schedule["time_of_day_taxonomy_id"],
                "precision_taxonomy_id": schedule["precision_taxonomy_id"],
            }

            for activity in activities:
                activity_date = itinerary_start_date + timedelta(days=activity["day"] - 1)
                if (
                    embarkation_date is None
                    and (
                        activity["embarkation_direction_taxonomy_id"] == self.EMBARKATION_DIRECTION_EMBARK_TX
                        or activity["embarkation_direction_taxonomy_id"]
                        == self.EMBARKATION_DIRECTION_2_WAY_TX
                    )
                    and activity_date >= from_time
                ):
                    embarkation_date = activity_date
                    embarkation_day = activity["day"]
                    embarkation_relative_time = {
                        "time": str(activity["time"]) if activity["time"] is not None else None,
                        "time_of_day_taxonomy_id": activity["time_of_day_taxonomy_id"],
                        "precision_taxonomy_id": activity["precision_taxonomy_id"],
                    }
                    break

            if embarkation_date is None:
                continue

            itinerary_end_date = itinerary_start_date + timedelta(days=max_length_days - 1)
            disembarkation_date = itinerary_end_date if itinerary_end_date <= to_time else None
            disembarkation_day = max_length_days - embarkation_day + 1

            for activity in reversed(activities):
                activity_date = itinerary_start_date + timedelta(days=activity["day"] - 1)
                if (
                    disembarkation_date is None
                    and (
                        activity["embarkation_direction_taxonomy_id"]
                        == self.EMBARKATION_DIRECTION_DISEMBARK_TX
                        or activity["embarkation_direction_taxonomy_id"]
                        == self.EMBARKATION_DIRECTION_2_WAY_TX
                    )
                    and embarkation_date < activity_date <= to_time
                ):
                    disembarkation_date = activity_date
                    disembarkation_day = activity["day"]
                    break

            if disembarkation_date is not None:
                options.append(
                    {
                        "embarkation_date": embarkation_date,
                        "disembarkation_date": disembarkation_date,
                        "embarkation_day": embarkation_day,
                        "disembarkation_day": disembarkation_day,
                        "embarkation_relative_time": embarkation_relative_time,
                    }
                )

        if not options:
            return [[], None, 0, 0, 0]

        best_option = max(
            options, key=lambda option: option["disembarkation_day"] - option["embarkation_day"] + 1
        )
        technical_length_days = best_option["disembarkation_day"] - best_option["embarkation_day"] + 1
        financial_length_days, financial_offset_days = self._get_financial_length_and_offset_days(
            best_option, activities, max_length_days
        )

        return [
            [str(best_option["embarkation_date"])[0:10]],
            best_option["embarkation_relative_time"],
            technical_length_days,
            financial_length_days,
            financial_offset_days,
        ]

    def _get_itinerary_start_dates_for_partial_itinerary(self, max_length_days, schedule):
        search_from = datetime.strptime(self.from_date, "%Y-%m-%d") - timedelta(max_length_days)
        search_to = (
            datetime.strptime(self.from_date, "%Y-%m-%d")
            + timedelta(max_length_days)
            + timedelta(hours=23, minutes=59, seconds=59)
        )
        return self._get_start_dates(search_from, search_to, 0, schedule)

    def _get_financial_length_and_offset_days(self, option, activities, max_length_days):
        start_day = 1
        end_day = max_length_days

        for activity in activities:
            if (
                activity["day"] <= option["embarkation_day"]
                and activity["embarkation_type_taxonomy_id"] == self.EMBARKATION_TYPE_FINANCIAL_TX
                and (
                    activity["embarkation_direction_taxonomy_id"] == self.EMBARKATION_DIRECTION_EMBARK_TX
                    or activity["embarkation_direction_taxonomy_id"] == self.EMBARKATION_DIRECTION_2_WAY_TX
                )
            ):
                start_day = activity["day"]

            if (
                activity["day"] >= option["disembarkation_day"]
                and activity["embarkation_type_taxonomy_id"] == self.EMBARKATION_TYPE_FINANCIAL_TX
                and (
                    activity["embarkation_direction_taxonomy_id"] == self.EMBARKATION_DIRECTION_DISEMBARK_TX
                    or activity["embarkation_direction_taxonomy_id"] == self.EMBARKATION_DIRECTION_2_WAY_TX
                )
            ):
                end_day = activity["day"]

        return end_day - start_day + 1, start_day - option["embarkation_day"]

    def _get_matching_schedules(self):
        return self.plpy.execute(
            """
            SELECT
                schedules.id,
                schedules.from_time,
                schedules.to_time,
                schedules.frequency_taxonomy_id,
                relative_times.day,
                relative_times.time,
                relative_times.time_of_day_taxonomy_id,
                relative_times.precision_taxonomy_id
            FROM schedules
            INNER JOIN relative_times
                ON schedules.relative_time_id = relative_times.id
            WHERE
                schedules.cruise_id = {cruise_id} AND
                schedules.deleted_at IS NULL AND
                (DATE '{from_date}', DATE '{to_date}') OVERLAPS (schedules.from_time, schedules.to_time)
        """.format(
                cruise_id=self.cruise_id, from_date=self.from_date, to_date=self.to_date
            )
        )

    def _get_itinerary_length(self):
        return self.plpy.execute(
            """
            SELECT MAX(relative_times.day) AS days
            FROM programs
                LEFT JOIN program_relations ON programs.id = program_relations.parent_id
                LEFT JOIN relative_times ON program_relations.relative_time_id = relative_times.id
            WHERE programs.id = (SELECT itinerary_id FROM cruises WHERE id = {cruise_id})
                AND programs.deleted_at IS NULL
            GROUP BY programs.id
        """.format(
                cruise_id=self.cruise_id
            )
        )[0]["days"]

    def _get_activities(self):
        return self.plpy.execute(
            """
            SELECT
                program_relations.child_id AS id,
                program_relations.embarkation_type_taxonomy_id,
                program_relations.embarkation_direction_taxonomy_id,
                relative_times.day,
                relative_times.time,
                relative_times.time_of_day_taxonomy_id,
                relative_times.precision_taxonomy_id
            FROM program_relations
                LEFT JOIN relative_times ON program_relations.relative_time_id = relative_times.id
            WHERE
                program_relations.parent_id = (SELECT itinerary_id FROM cruises WHERE id = {cruise_id}) AND
                program_relations.embarkation_type_taxonomy_id != {embarkation_type_none_tx} AND
                program_relations.embarkation_direction_taxonomy_id != {embarkation_direction_none_tx}
            ORDER BY relative_times.day ASC
        """.format(
                cruise_id=self.cruise_id,
                embarkation_type_none_tx=self.EMBARKATION_TYPE_NONE_TX,
                embarkation_direction_none_tx=self.EMBARKATION_DIRECTION_NONE_TX,
            )
        )

    def _is_child_bed_policy_strict(self, available_device):
        return False
