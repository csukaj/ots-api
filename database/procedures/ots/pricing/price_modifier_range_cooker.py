from copy import copy
from datetime import datetime, timedelta
from operator import itemgetter

from ots.common.config import Config
from stylers.date_helpers import segmented_nights, datetime_to_str
from stylers.utils import execute_cached_query


class PriceModifierRangeCooker:
    def __init__(self, **keyword_parameters):
        self.plpy = keyword_parameters["plpy"]
        self.date_rangeable_type = keyword_parameters["date_rangeable_type"]
        self.date_rangeable_id = keyword_parameters["date_rangeable_id"]
        self.from_time = keyword_parameters["from_time"]
        self.to_time = keyword_parameters["to_time"]
        self.date_ranges = keyword_parameters["date_ranges"]
        self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER].sort(key=itemgetter("from_time"))

    def add_annual_price_modifiers(self):
        annual_price_modifiers_in_range = self._get_annual_price_modifiers(self.from_time, self.to_time)
        for additional_range in annual_price_modifiers_in_range:
            self._merge_additional_range_to_price_modifier_ranges(additional_range)

        self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER].sort(key=itemgetter("from_time"))

    def merge_multi_period_price_modifier_ranges(self):
        """
        not works because merged ranges don't have correct id so it can't have price elements
        should have id of the ranges merged into it
        but range merge is needed. TODO: debug this situation
        """

        periods_of_price_modifiers = {}

        # collect periods for price modifiers
        for period in self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER]:
            for price_modifier_id in period["price_modifier_ids"]:
                periods_of_price_modifiers.setdefault(price_modifier_id, []).append(period)

        for (
            multi_period_price_modifier_id,
            price_modifier_periods,
        ) in periods_of_price_modifiers.iteritems():
            if len(price_modifier_periods) > 1:
                price_modifier_periods.sort(key=itemgetter("from_time"))
                (merged_periods, periods_to_remove_from) = self.find_mergeable_ranges(price_modifier_periods)
                self.remove_multiperiod_price_modifier_from_normal_ranges(
                    multi_period_price_modifier_id, periods_to_remove_from
                )
                self.add_multi_period_ranges(merged_periods, multi_period_price_modifier_id)

        # remove empty discount periods - it would be an error. They needed for price calculation. (we iterate over them for getting price elements.)
        # self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER] = filter(lambda x: len(x['price_modifier_ids']) > 0, self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER])

    def find_mergeable_ranges(self, price_modifier_date_ranges):
        merged_periods = []
        periods_to_remove_from = []
        for price_modifier_date_range in price_modifier_date_ranges:
            range_to_check = copy(price_modifier_date_range)
            range_to_check["from_time"] = datetime.strptime(range_to_check["from_time"], "%Y-%m-%d %H:%M:%S")
            range_to_check["to_time"] = datetime.strptime(range_to_check["to_time"], "%Y-%m-%d %H:%M:%S")

            # if mergeable, add both period to the removable list.
            # if mergeable, extend merged list last item validity, else simply append to it
            if merged_periods and self._are_ranges_mergeable(merged_periods[-1], range_to_check):
                periods_to_remove_from.append(self._range_dates_to_string(merged_periods[-1]))
                periods_to_remove_from.append(self._range_dates_to_string(range_to_check))
                merged_periods[-1]["to_time"] = range_to_check["to_time"]
                if type(merged_periods[-1]["id"]) is list:
                    merged_periods[-1]["id"].append(range_to_check["id"])
                else:
                    merged_periods[-1]["id"] = [merged_periods[-1]["id"]]
            else:
                merged_periods.append(range_to_check)
        return merged_periods, periods_to_remove_from

    def add_multi_period_ranges(self, merged_price_modifier_periods, multi_period_price_modifier_id):
        # add new ranges
        for multi_period_date_range in merged_price_modifier_periods:
            # multi_period_date_range.pop('id', None)
            multi_period_date_range["from_time"] = multi_period_date_range["from_time"].strftime(
                "%Y-%m-%d %H:%M:%S"
            )
            multi_period_date_range["to_time"] = multi_period_date_range["to_time"].strftime(
                "%Y-%m-%d %H:%M:%S"
            )
            multi_period_date_range["price_modifier_ids"] = [multi_period_price_modifier_id]

            found = False

            for discount_range in self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER]:
                if (
                    discount_range["from_time"] == multi_period_date_range["from_time"]
                    and discount_range["to_time"] == multi_period_date_range["to_time"]
                ):
                    discount_range["price_modifier_ids"].extend(multi_period_date_range["price_modifier_ids"])
                    discount_range["price_modifier_ids"] = list(set(discount_range["price_modifier_ids"]))
                    found = True
            if not found:
                self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER].append(multi_period_date_range)

    def remove_multiperiod_price_modifier_from_normal_ranges(
        self, multi_period_price_modifier_id, periods_to_remove_from
    ):
        # remove from existing(and already merged price modifier ranges)
        for period in self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER]:
            for remove_period in periods_to_remove_from:
                if (
                    period["from_time"] == remove_period["from_time"]
                    and period["to_time"] == remove_period["to_time"]
                    and multi_period_price_modifier_id in period["price_modifier_ids"]
                ):
                    period["price_modifier_ids"].remove(multi_period_price_modifier_id)

    @staticmethod
    def _range_dates_to_string(date_range):
        date_range2 = copy(date_range)
        date_range2["from_time"] = date_range2["from_time"].strftime("%Y-%m-%d %H:%M:%S")
        date_range2["to_time"] = date_range2["to_time"].strftime("%Y-%m-%d %H:%M:%S")
        return date_range2

    @staticmethod
    def _are_ranges_mergeable(first, second):
        return first["to_time"] + timedelta(seconds=1) == second["from_time"]

    def _get_annual_price_modifiers(self, from_time, to_time):
        sql = """
            SELECT 
                date_ranges.*,
                array_agg("price_modifier_periods"."price_modifier_id" ORDER BY "price_modifier_periods"."price_modifier_id") AS "price_modifier_ids"
            FROM price_modifiers
              JOIN price_modifier_periods ON price_modifiers.id = price_modifier_periods.price_modifier_id
              JOIN date_ranges ON price_modifier_periods.date_range_id = date_ranges.id
            WHERE
              date_ranges.date_rangeable_type = '{date_rangeable_type}'
              AND date_ranges.date_rangeable_id = {date_rangeable_id}
              AND is_annual = TRUE
              AND "date_ranges"."type_taxonomy_id" = {type_date_range}
              AND price_modifiers.deleted_at IS NULL
              AND price_modifier_periods.deleted_at IS NULL
              AND date_ranges.deleted_at IS NULL
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
              AND TO_CHAR(from_time, 'YYYY') <= TO_CHAR(DATE '{from_time}', 'YYYY')
              AND TO_CHAR(to_time, 'YYYY') <= TO_CHAR(DATE '{to_time}', 'YYYY')
            GROUP BY "date_ranges"."id"
            ORDER BY TO_CHAR(from_time,'MMDD') ASC, from_time DESC
        """
        sql = sql.format(
            date_rangeable_type=self.date_rangeable_type,
            date_rangeable_id=str(self.date_rangeable_id),
            from_time="{:%Y-%m-%d %H:%M:%S}".format(from_time),
            to_time="{:%Y-%m-%d %H:%M:%S}".format(to_time),
            type_date_range=Config.DATE_RANGE_TYPE_PRICE_MODIFIER,
        )
        return execute_cached_query(self.plpy, sql)

    def _merge_additional_range_to_price_modifier_ranges(self, additional_range):
        additional_range["from_time"] = datetime_to_str(additional_range["from_time"])
        additional_range["to_time"] = datetime_to_str(additional_range["to_time"])

        found_annual_times = self._find_years_for_annual_range(
            self.from_time, self.to_time, additional_range["from_time"], additional_range["to_time"]
        )

        for found_annual_time in found_annual_times:
            found = False
            annual_range = copy(additional_range)
            annual_range["from_time"] = found_annual_time["from_time"]
            annual_range["to_time"] = found_annual_time["to_time"]

            for ddrange in self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER]:
                if (
                    ddrange["id"] == annual_range["id"]
                    and ddrange["from_time"] == annual_range["from_time"]
                    and ddrange["to_time"] == annual_range["to_time"]
                ):
                    ddrange["price_modifier_ids"].extend(annual_range["price_modifier_ids"])
                    ddrange["price_modifier_ids"] = list(set(ddrange["price_modifier_ids"]))
                    found = True
            if not found:
                self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER].append(annual_range)

    @staticmethod
    def _replace_year_in_stringdate(original_date, year):
        replaced_date = datetime.strptime(original_date, "%Y-%m-%d %H:%M:%S").replace(year)
        return datetime.strftime(replaced_date, "%Y-%m-%d %H:%M:%S")

    @staticmethod
    def _add_year_to_range(time_range, year=1):
        time_range = copy(time_range)
        time_range["from_time"] = PriceModifierRangeCooker._replace_year_in_stringdate(
            time_range["from_time"], int(time_range["from_time"][:4]) + year
        )
        time_range["to_time"] = PriceModifierRangeCooker._replace_year_in_stringdate(
            time_range["to_time"], int(time_range["to_time"][:4]) + year
        )
        return time_range

    def _find_years_for_annual_range(self, from_time, to_time, range_from_time, range_to_time):
        from_time = "{:%Y-%m-%d %H:%M:%S}".format(from_time)
        to_time = "{:%Y-%m-%d %H:%M:%S}".format(to_time)
        open_ending = True
        time_range = {"from_time": range_from_time, "to_time": range_to_time}

        while (
            segmented_nights(
                from_time,
                to_time,
                time_range["from_time"],
                time_range["to_time"],
                second_range_has_open_ending=open_ending,
            )
            == 0
            and time_range["from_time"] < to_time
        ):
            time_range = self._add_year_to_range(time_range)

        ranges = []
        if segmented_nights(
            from_time,
            to_time,
            time_range["from_time"],
            time_range["to_time"],
            second_range_has_open_ending=open_ending,
        ):
            ranges.append(time_range)

        time_range = self._add_year_to_range(time_range)
        if (
            segmented_nights(
                from_time,
                to_time,
                time_range["from_time"],
                time_range["to_time"],
                second_range_has_open_ending=open_ending,
            )
            > 0
        ):
            ranges.append(time_range)

        return ranges
