from copy import copy, deepcopy
from datetime import datetime, timedelta

from ots.common.config import Config
from stylers.date_helpers import month_and_day_in_range
from stylers.utils import execute_cached_query


class DateRangeCooker:
    def __init__(self, **keyword_parameters):
        self.plpy = keyword_parameters["plpy"]
        self.date_rangeable_type = keyword_parameters["date_rangeable_type"]
        self.date_rangeable_id = keyword_parameters["date_rangeable_id"]
        self.from_time = keyword_parameters["from_time"]
        self.to_time = keyword_parameters["to_time"]
        self.date_ranges = keyword_parameters["date_ranges"]
        self.nights = (self.to_time - self.from_time).days

    def is_covered(self):
        return self._get_uncovered_nights() == 0

    def _get_uncovered_nights(self):
        holiday_nights = self._generate_holiday_nights()
        uncovered_nights = self._check_open_nights(holiday_nights)
        if not uncovered_nights:
            return 0

        # there are uncovered nights: cook date ranges
        uncovered_date_range = self._create_date_range_from_day_list(uncovered_nights)
        last_date_ranges = self._get_last_open_date_ranges(
            uncovered_date_range["from_time"], uncovered_date_range["to_time"]
        )
        self._cook_date_ranges(uncovered_nights, last_date_ranges)

        # check again if we could cooked date ranges for whole period
        uncovered_nights = self._check_open_nights(holiday_nights)
        return len(uncovered_nights)

    def _generate_holiday_nights(self):
        # generate holiday nights array between from_time and to_time
        holiday_nights = []
        day = copy(self.from_time)
        while "{:%Y-%m-%d}".format(day) < "{:%Y-%m-%d}".format(self.to_time):
            holiday_nights.append("{:%Y-%m-%d}".format(day))
            day = day + timedelta(days=1)
        return holiday_nights

    def _check_open_nights(self, holiday_nights):
        # returns uncovered nights
        range_nights = copy(holiday_nights)
        for date_range in self.date_ranges[Config.DATE_RANGE_TYPE_OPEN]:
            if date_range["minimum_nights"] <= self.nights:
                from_day = self._get_time_as_string(date_range["from_time"])[:10]
                to_day = self._get_time_as_string(date_range["to_time"])[:10]
                for night in holiday_nights:
                    if from_day <= night <= to_day and night in range_nights:
                        range_nights.remove(night)
        return range_nights

    @staticmethod
    def _create_date_range_from_day_list(uncovered_nights):
        # creates an interval from day list
        if not uncovered_nights:
            return None

        return {
            "from_time": datetime.strptime(uncovered_nights[0], "%Y-%m-%d"),
            "to_time": datetime.strptime(uncovered_nights[len(uncovered_nights) - 1], "%Y-%m-%d")
            + timedelta(hours=23, minutes=59, seconds=59),
        }

    def _get_last_open_date_ranges(self, from_time, to_time):
        sql = """
            SELECT *
            FROM date_ranges
            WHERE
                date_rangeable_type = '{date_rangeable_type}'
                AND date_rangeable_id = {date_rangeable_id}
                AND type_taxonomy_id = {date_range_type_tx_id}
                AND (
                    DATE(concat(extract(YEAR FROM from_time), substr('{from_time}', 5)))
                        BETWEEN date_ranges.from_time AND to_time
                    OR
                    DATE(concat(extract(YEAR FROM to_time), substr('{to_time}', 5)))
                        BETWEEN date_ranges.from_time AND to_time
                    OR
                    (
                        TO_CHAR(DATE '{from_time}', 'MMDD') <= TO_CHAR(DATE '{to_time}', 'MMDD')
                        AND
                        TO_CHAR(DATE '{from_time}', 'MMDD') <= TO_CHAR(from_time, 'MMDD')
                        AND
                        TO_CHAR(DATE '{to_time}', 'MMDD') >= TO_CHAR(to_time, 'MMDD')
                    )
                    OR
                    (
                        TO_CHAR(DATE '{from_time}', 'MMDD') > TO_CHAR(DATE '{to_time}', 'MMDD')
                        AND
                        (
                            TO_CHAR(from_time, 'MMDD') BETWEEN TO_CHAR(DATE '{from_time}', 'MMDD') AND '1231'
                            OR
                            TO_CHAR(to_time, 'MMDD') BETWEEN '0101' AND TO_CHAR(DATE '{to_time}', 'MMDD')
                        ) 
                    )
                )
                AND TO_CHAR(to_time, 'YYYY') <= TO_CHAR(DATE '{to_time}', 'YYYY')
                AND date_ranges.deleted_at IS NULL
                AND  from_time < DATE '{to_time}'
            ORDER BY TO_CHAR(from_time,'MMDD') ASC, from_time DESC
        """
        sql = sql.format(
            date_rangeable_type=str(self.date_rangeable_type),
            date_rangeable_id=str(self.date_rangeable_id),
            from_time="{:%Y-%m-%d %H:%M:%S}".format(from_time),
            to_time="{:%Y-%m-%d %H:%M:%S}".format(to_time),
            date_range_type_tx_id=Config.DATE_RANGE_TYPE_OPEN,
        )
        return execute_cached_query(self.plpy, sql)

    def _cook_date_ranges(self, uncovered_nights_list, last_date_ranges):

        uncovered_nights = copy(uncovered_nights_list)
        for act_range in last_date_ranges:
            act_uncovered_nights = copy(uncovered_nights)
            mod_range = {"from_time": None, "to_time": None}

            for day in uncovered_nights:
                day_as_datetime = datetime.strptime(day, "%Y-%m-%d")

                if month_and_day_in_range(day, act_range):
                    if mod_range["from_time"] is None:
                        mod_range["from_time"] = day_as_datetime

                    if mod_range["to_time"] is None or mod_range["to_time"].date() <= day_as_datetime.date():
                        mod_range["to_time"] = day_as_datetime + timedelta(hours=23, minutes=59, seconds=59)
                        act_uncovered_nights.remove(day)

            uncovered_nights = act_uncovered_nights

            if mod_range["from_time"] is not None:
                new_range = deepcopy(act_range)
                new_range["from_time"] = mod_range["from_time"]
                new_range["to_time"] = mod_range["to_time"]
                self.date_ranges[Config.DATE_RANGE_TYPE_OPEN].append(new_range)

        # create open interval from newly created date range (only if we actually created any)
        if self.date_ranges[Config.DATE_RANGE_TYPE_OPEN] and last_date_ranges:
            if isinstance(self.date_ranges[Config.DATE_RANGE_TYPE_OPEN][-1]["to_time"], str):
                self.date_ranges[Config.DATE_RANGE_TYPE_OPEN][-1]["to_time"] = datetime.strptime(
                    self.date_ranges[Config.DATE_RANGE_TYPE_OPEN][-1]["to_time"], "%Y-%m-%d %H:%M:%S"
                )

    @staticmethod
    def _get_time_as_string(time, date_format="%Y-%m-%d %H:%M:%S"):
        return time if isinstance(time, str) else time.strftime(date_format)
