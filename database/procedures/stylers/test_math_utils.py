import unittest

from stylers.math_utils import DateInterval
from stylers.math_utils import MathUtils
from datetime import datetime


class TestDateInterval(unittest.TestCase):

    def test_length(self):
        a = DateInterval(datetime(year=2019, month=06, day=24), datetime(year=2019, month=06, day=26))
        length = len(a)

        self.assertEqual(length, 3)

    def test_intersection(self):
        a = DateInterval(datetime(year=2019, month=06, day=24), datetime(year=2019, month=06, day=26))
        b = DateInterval(datetime(year=2019, month=06, day=2), datetime(year=2019, month=06, day=25))
        c = DateInterval(datetime(year=2019, month=06, day=24), datetime(year=2019, month=06, day=25))
        intersection = DateInterval.intersection(a, b)
        self.assertEqual(c, intersection)

    def test_intersection2(self):
        a = DateInterval(datetime(year=2019, month=06, day=12), datetime(year=2019, month=06, day=26), False, True)
        b = DateInterval(datetime(year=2019, month=06, day=1), datetime(year=2019, month=06, day=20), True, False)
        c = DateInterval(datetime(year=2019, month=06, day=12), datetime(year=2019, month=06, day=20), False, False)
        intersection = DateInterval.intersection(a, b)
        self.assertEqual(c, intersection)

    def test_intersection3(self):
        a = DateInterval(datetime(year=2019, month=06, day=12), datetime(year=2019, month=06, day=26), True, True)
        b = DateInterval(datetime(year=2019, month=06, day=1), datetime(year=2019, month=06, day=20), True, True)
        c = DateInterval(datetime(year=2019, month=06, day=12), datetime(year=2019, month=06, day=20), True, True)
        intersection = DateInterval.intersection(a, b)
        self.assertEqual(c, intersection)

    def test_exclusives(self):
        a = DateInterval(datetime(year=2019, month=06, day=12), datetime(year=2019, month=06, day=26), True, True)
        b = DateInterval(datetime(year=2019, month=06, day=1), datetime(year=2019, month=06, day=20), True, True)
        c = DateInterval(datetime(year=2019, month=06, day=12), datetime(year=2019, month=06, day=20), True, True)
        answer = DateInterval.exclusives(a, b, c)
        self.assertFalse(answer)

    def test_exclusives2(self):
        a = DateInterval(datetime(year=2019, month=06, day=12), datetime(year=2019, month=06, day=26), True, True)
        b = DateInterval(datetime(year=2019, month=06, day=1), datetime(year=2019, month=06, day=2), True, True)
        c = DateInterval(datetime(year=2019, month=06, day=2), datetime(year=2019, month=06, day=4), True, True)
        answer = DateInterval.exclusives(a, b, c)
        self.assertTrue(answer)

    def test_date_iterator(self):
        a = DateInterval(datetime(year=2019, month=06, day=24), datetime(year=2019, month=06, day=26), False, False)
        length = len(list(a.dates()))
        self.assertEqual(length, 3)

    def test_date_iterator2(self):
        a = DateInterval(datetime(year=2019, month=06, day=24), datetime(year=2019, month=06, day=26), True, True)
        length = len(list(a.dates()))
        self.assertEqual(length, 1)


class TestMathUtils(unittest.TestCase):
    def test_knap_sack(self):
        items = [
            # item, weight, value, qty
            ("orange", 10, 20, 1),
            ("apple", 20, 30, 3),
            ("pear", 30, 30, 3)
        ]
        res = MathUtils.knap_sack(items, 50)
        qty = sum([qty for qty, obj in res])
        self.assertEqual(qty, 3)  # 2 apple, 1 orange

    def test_of_choose_the_bigger_weight_when_not_worse(self):
        items = [
            # item, weight, value, qty
            ("orange", 30, 30, 1),
            ("apple", 40, 30, 1),
        ]
        res = MathUtils.knap_sack(
            grouped_items=items,
            limit=50,
            give_bigger_when_the_value_is_same=True
        )

        self.assertEqual([obj[0] for qty, obj in res][0], "apple")  # 2 apple, 1 orange

    def test_of_choose_the_bigger_weight_when_not_worse2(self):
        items = [
            # item, weight, value, qty
            ("apple", 40, 30, 1),
            ("orange", 30, 30, 1),
        ]
        res = MathUtils.knap_sack(
            grouped_items=items,
            limit=50,
            give_bigger_when_the_value_is_same=True
        )

        self.assertEqual([obj[0] for qty, obj in res][0], "apple")  # 2 apple, 1 orange

    def test_of_choose_the_bigger_weight_when_not_worse3(self):
        items = [
            # item, weight, value, qty
            ("orange", 10, 20, 1),
            ("apple", 40, 30, 3),
            ("pear", 30, 30, 3)
        ]
        res = MathUtils.knap_sack(
            grouped_items=items,
            limit=50,
            give_bigger_when_the_value_is_same=True
        )
        res = list(res)

        contains_apple = "apple" in [obj[0] for qty, obj in res]
        contains_orange = "orange" in [obj[0] for qty, obj in res]
        self.assertTrue(contains_apple and contains_orange)  # 2 apple, 1 orange

    def test_knap_sack_filter_method_when_there_is_no_valid_combinations(self):
        items = [
            # item, weight, value, qty
            ("orange", 10, 20, 1),
            ("apple", 20, 30, 3),
            ("pear", 30, 30, 3)
        ]
        res = res = MathUtils.knap_sack(
            grouped_items=items,
            limit=50,
            give_bigger_when_the_value_is_same=False,
            is_this_combination_valid=lambda x: False
        )
        qty = sum([qty for qty, obj in res])
        self.assertEqual(qty, 0)

    def test_knap_sack_filter_method_when_there_is_no_valid_steps(self):
        items = [
            # item, weight, value, qty
            ("orange", 10, 20, 1),
            ("apple", 20, 30, 3),
            ("pear", 30, 30, 3)
        ]
        res = res = MathUtils.knap_sack(
            grouped_items=items,
            limit=50,
            give_bigger_when_the_value_is_same=False,
            can_this_item_be_added=lambda combination, item: False
        )
        qty = sum([qty for qty, obj in res])
        self.assertEqual(qty, 0)

    def test_knap_sack_filter_method_when_there_is_only_one_valid_combination(self):
        items = [
            # item, weight, value, qty
            ("orange", 10, 20, 1),
            ("apple", 20, 30, 3),
            ("pear", 30, 30, 3)
        ]

        can_be_more = lambda combination : sum([qty for qty, obj in combination]) < 2
        is_the_item_apple = lambda item: item[0] == 'apple'
        res = res = MathUtils.knap_sack(
            grouped_items=items,
            limit=50,
            give_bigger_when_the_value_is_same=False,
            # this defines the way to go
            can_this_item_be_added=lambda combination, item: can_be_more(combination) and is_the_item_apple(item),
            # this is the last validation
            is_this_combination_valid=lambda x: x[0][0] == 2 and x[0][1][0] == 'apple' and len(x) == 1  # 2 apples
        )
        qty = sum([qty for qty, obj in res])
        self.assertEqual(qty, 2)

    def test_knap_sack_filter_method_when_there_are_more_valid_combinations(self):
        items = [
            # item, weight, value, qty
            ("orange", 10, 20, 1),
            ("apple", 20, 30, 3),
            ("pear", 30, 30, 3)
        ]

        def _less_than_n_apples(combination, n):
            for qty, item in combination:
                if item[0] == 'apple' and qty >= n:
                    return False
            return True

        def way(combination, item):
            return item[0] != 'apple' or _less_than_n_apples(combination, 2)

        res = res = MathUtils.knap_sack(
            grouped_items=items,
            limit=60,
            give_bigger_when_the_value_is_same=False,
            # this defines the way to go
            can_this_item_be_added=way,
            # this is the last validation
            is_this_combination_valid=lambda combination: _less_than_n_apples(combination, 2)
        )

        self.assertEqual([qty for qty, obj in res], [1, 1, 1])
