import unittest

from ots.price_modifier.merged_price_modifier import MergedPriceModifier
from stylers.math_utils import DateInterval
from datetime import datetime, timedelta
from ots.common.config import Config


class MyOfffer:
    def __init__(self, frequency, num_of_free, maximum):
        self.frequency = frequency
        self.maximum = maximum
        self.num_of_free = num_of_free

    def get_cumulation_maximum(self):
        return self.maximum

    def get_cumulation_frequency(self):
        return self.frequency

    def get_discounted_nights(self):
        return self.num_of_free

class MyFreeNightPriceModifier:
    pm_id = 0

    def __init__(self, frequency, num_of_free, maximum, date_ranges=[], request = None):
        MyFreeNightPriceModifier.pm_id += 1
        self.id = MyFreeNightPriceModifier.pm_id
        self.offer = MyOfffer(frequency=frequency, num_of_free=num_of_free, maximum=maximum)
        self.date_ranges = {}
        self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER] = map(lambda x: {
            'from_time': x.from_date,
            'to_time': x.to_date,
            'price_modifier_ids': list(range(1000))
        }, date_ranges)

        if request is None:
            request = {
                "from_time": datetime(year=2019, month=6, day=1),
                "to_time": datetime(year=2019, month=6, day=30)
            }

        self.request_from_time = request['from_time']
        self.request_to_time = request['to_time']

    def is_valid_free_night_price_modifier(self):
        return True

    def get_id(self):
        return self.id


class TestMergedPriceModifier(unittest.TestCase):
    def test__get_coverage(self):
        # 10 nights
        interval = DateInterval(
            datetime(year=2019, month=6, day=1),
            datetime(year=2019, month=6, day=11),
            from_open=False,
            to_open=True
        )

        # 5=3 (max 3x)
        free_night = MyFreeNightPriceModifier(5, 2, 3)

        coverage = MergedPriceModifier._get_coverage(interval, free_night)
        self.assertEqual(coverage['discounted_nights'], 4)
        self.assertEqual(coverage['uncovered_nights'], 0)

    def test__get_coverage2(self):
        # 9 nights
        interval = DateInterval(
            datetime(year=2019, month=6, day=1),
            datetime(year=2019, month=6, day=10),
            from_open=False,
            to_open=True
        )

        # 5=3 (max 3x)
        free_night = MyFreeNightPriceModifier(5, 2, 3)

        coverage = MergedPriceModifier._get_coverage(interval, free_night)
        self.assertEqual(coverage['discounted_nights'], 2)
        self.assertEqual(coverage['uncovered_nights'], 4)

    def test__get_sum_of_discounted_nights_for_combination_for_single(self):
        interval = DateInterval(
            datetime(year=2019, month=6, day=1),
            datetime(year=2019, month=6, day=11),
            from_open=False,
            to_open=True
        )  # 10 nights
        free_night = MyFreeNightPriceModifier(5, 2, 3)

        mergable = MergedPriceModifier._get_optimum_for_intervals([interval], [free_night])
        self.assertEqual(mergable['discounted_nights'], 4)

    def test__get_sum_of_discounted_nights_for_combination(self):
        interval1 = DateInterval(
            datetime(year=2019, month=6, day=1),
            datetime(year=2019, month=6, day=10),
            from_open=False,
            to_open=True
        )  # 9 nights
        free_night1 = MyFreeNightPriceModifier(5, 2, 3, [
            interval1
        ])  # 5=3

        interval2 = DateInterval(
            datetime(year=2019, month=6, day=11),
            datetime(year=2019, month=6, day=20),
            from_open=False,
            to_open=True
        )  # 9 nights
        free_night2 = MyFreeNightPriceModifier(5, 2, 3, [
            interval2
        ])  # 5=3

        # it has enough uncovered nights for a free night
        mergable = MergedPriceModifier._get_optimum_for_intervals([interval1, interval2],
                                                                                     [free_night1, free_night2])
        self.assertEqual(mergable['discounted_nights'], 6)

    def test__get_mergable_free_night_modifiers(self):
        price_modifiers = [
            MyFreeNightPriceModifier(
                frequency=5,
                num_of_free=2,
                maximum=3,
                date_ranges=[
                    # 9 nights
                    DateInterval(
                        datetime(year=2019, month=6, day=1),
                        datetime(year=2019, month=6, day=10)
                    )
                ]
            ),
            MyFreeNightPriceModifier(
                frequency=10,
                num_of_free=3,
                maximum=3,
                date_ranges=[
                    DateInterval(
                        datetime(year=2019, month=6, day=1),
                        datetime(year=2019, month=6, day=10)
                    )]
            ),
            MyFreeNightPriceModifier(
                frequency=7,
                num_of_free=2,
                maximum=3,
                date_ranges=[
                    DateInterval(
                        datetime(year=2019, month=6, day=01),
                        datetime(year=2019, month=6, day=10)
                    )]
            ),
            MyFreeNightPriceModifier(
                frequency=5,
                num_of_free=2,
                maximum=3,
                date_ranges=[
                    # 9 nights
                    DateInterval(
                        datetime(year=2019, month=6, day=11),
                        datetime(year=2019, month=6, day=20)
                    )
                ]
            )
        ]

        mergables = MergedPriceModifier._get_optimum(price_modifiers)

        self.assertEqual(len(mergables['combination']), 2)
