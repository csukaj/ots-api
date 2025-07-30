from itertools import chain, combinations, groupby
from datetime import datetime, timedelta
import math


class DateInterval:
    def __init__(self, from_date, to_date, from_open=False, to_open=False):
        self.from_date = from_date
        self.to_date = to_date
        self.from_open = from_open
        self.to_open = to_open

    @staticmethod
    def _intersection(a, b):

        # play with microseconds to handle open and closed ends
        a_from_delta = timedelta(microseconds=1) if a.from_open else timedelta(microseconds=0)
        b_from_delta = timedelta(microseconds=1) if b.from_open else timedelta(microseconds=0)
        from_deltas = [a_from_delta, b_from_delta]

        a_to_delta = timedelta(microseconds=-1) if a.to_open else timedelta(microseconds=0)
        b_to_delta = timedelta(microseconds=-1) if b.to_open else timedelta(microseconds=0)
        to_deltas = [a_to_delta, b_to_delta]

        from_values = [a.from_date + a_from_delta, b.from_date + b_from_delta]
        to_values = [a.to_date + a_to_delta, b.to_date + b_to_delta]

        begin_id, begin = max(enumerate(from_values), key=lambda x: x[1])
        end_id, end = min(enumerate(to_values), key=lambda x: x[1])

        if begin < end:
            return DateInterval(
                begin - from_deltas[begin_id],
                end - to_deltas[end_id],
                begin.microsecond > 0,
                end.microsecond > 0,
            )
        else:
            return DateInterval(
                datetime(year=1000, month=1, day=1), datetime(year=1000, month=1, day=1), True, True
            )

    @staticmethod
    def intersection(*params):
        # start with maximum interval
        intersection = DateInterval(datetime(year=1000, month=1, day=1), datetime(year=9999, month=12, day=1))
        for param in params:
            intersection = DateInterval._intersection(intersection, param)
        return intersection

    @staticmethod
    def exclusives(*intervals):
        """
        function will return true if none of given intervals has intersection to each other
        otherwise false
        :param intervals:
        :return: boolean
        """

        for a, b in combinations(intervals, 2):
            inter = DateInterval._intersection(a, b)
            if len(inter) != 0:
                return False

        return True

    def __len__(self):
        delta = self.to_date - self.from_date
        days = delta.days + 1 - self.from_open - self.to_open
        return max(days, 0)

    def dates(self):
        for i in range(0, len(self) + self.from_open + self.to_open):
            to_yield = self.from_date + timedelta(days=i)

            if self.from_open and to_yield == self.from_date:
                continue

            if self.to_open and to_yield == self.to_date:
                continue

            yield to_yield

    def __str__(self):
        start = "(" if self.from_open else "["
        end = ")" if self.to_open else "]"
        return start + str(self.from_date) + "; " + str(self.to_date) + end

    def __eq__(self, other):
        return (
            self.from_date == other.from_date
            and self.to_date == other.to_date
            and self.to_open == other.to_open
            and self.from_open == other.from_open
        )


class SortedList:
    def __init__(self, val):
        self.left = None
        self.right = None
        self.val = val


class MathUtils:
    @staticmethod
    def powerset(iterable):
        "powerset([1,2,3]) --> () (1,) (2,) (3,) (1,2) (1,3) (2,3) (1,2,3)"
        s = list(iterable)
        return chain.from_iterable(combinations(s, r) for r in range(len(s) + 1))

    @staticmethod
    def powerset_from_2_elements(iterable):
        "powerset([1,2,3]) --> () (1,) (2,) (3,) (1,2) (1,3) (2,3) (1,2,3)"
        s = list(iterable)
        return chain.from_iterable(combinations(s, r) for r in range(2, len(s) + 1))

    @staticmethod
    def knap_sack(
        grouped_items,
        limit,
        give_bigger_when_the_value_is_same=False,
        can_this_item_be_added=lambda part_combination, item: True,
        is_this_combination_valid=lambda combination: True,
    ):
        """
        This is a modified iterative table filling knap snack algorithm.
        Knap snack finds the optimal quantity of items to reach the biggest value sum.
        :param grouped_items: list of (item, weight, value, available_number) ordered Ns
        :param limit: float

        This is modified to be an option to give the bigger weight when the value is the same.
        This modification does not have effect to the optimal value.
        :param give_bigger_when_the_value_is_same: bool

        This is also modified to be able to filter out invalid combinations
        :param is_valid_combination: function

        :return: list of (used number, group)
        """

        # Create N item tuples if there are N items, but do not create more than the weight limit.
        # This is because of we can only calculate optimum by checking the elements one by one.
        # For simplifying the algorithm, this hides the N variable from the given grouped_items,
        # so we don't have to check the quantity.
        get_maximum_quantity_to_create = lambda n, limit, wt: min(n, int(limit / wt))
        lists_of_items = (
            [(item, wt, val)] * get_maximum_quantity_to_create(n, limit, wt)
            for item, wt, val, n in grouped_items
        )
        items = sum(lists_of_items, [])  # merge lists

        # Allocate a matrix of values where the cell i,j :
        # M[i][j] = the value given by adding the "i"th item with satisfying the "j" weight limit.
        # This is the base of the algorithm the algorithm.
        # The bigger limit we see, the more value can be reached by the items.
        # When the part-limit reaches the final limit, we can check which items are used for the biggest value sum
        values = [[0 for w in range(limit + 1)] for j in xrange(len(items) + 1)]

        # Create a boolean matrix with the same size as the values matrix, where the cell i,j:
        # M[i][j] = the value of values[i][j] is the same with the previous value but this item is heavier.
        # This is used for detecting bigger items when the "give_bigger_when_the_value_is_same" mode is active.
        same_but_bigger = [[False for w in range(limit + 1)] for j in xrange(len(items) + 1)]

        def _get_part_combination(max_i, current_max_weight, current_max_weight_checking):
            result = []
            for i, (item, wt, val) in reversed(list(enumerate(items))[: max_i + 1]):
                was_added = (
                    values[i + 1][current_max_weight_checking] != values[i][current_max_weight_checking]
                    or same_but_bigger[i + 1][current_max_weight_checking]
                )

                if was_added and current_max_weight >= wt:
                    result.append(items[i])
                    current_max_weight -= wt
            return [[len(list(grp)), item] for item, grp in groupby(sorted(result))]

        for i, (item, wt, val) in enumerate(items):
            for current_max_weight in xrange(1, limit + 1):
                if wt > current_max_weight:
                    # This item is heavier than the current checking level, we don't use this item.
                    # The value would stay the same.
                    values[i + 1][current_max_weight] = values[i][current_max_weight]
                else:
                    # We can add this "item" because this fills in the current_max_weight.
                    # But the question is: should we add it?
                    # If the value is bigger than current, yes we should.

                    this_val = max(values[i][current_max_weight], values[i + 1][current_max_weight - 1])
                    this_val_if_we_add_this_item = values[i][current_max_weight - wt] + val

                    # Check if we can add this item
                    part_combination = _get_part_combination(i, current_max_weight, current_max_weight - 1)
                    if can_this_item_be_added(part_combination, (item, wt, val)):
                        # Hold the bigger value:
                        values[i + 1][current_max_weight] = max(this_val, this_val_if_we_add_this_item)

                        # Handle "give_bigger_when_the_value_is_same" option
                        have_the_same_value = this_val == this_val_if_we_add_this_item
                        is_this_item_the_first = i == 0
                        is_this_item_heavier = items[i][1] > items[i - 1][1]

                        if (
                            give_bigger_when_the_value_is_same
                            and have_the_same_value
                            and not is_this_item_the_first
                            and is_this_item_heavier
                        ):
                            same_but_bigger[i + 1][current_max_weight] = True
                    else:
                        values[i + 1][current_max_weight] = this_val

        # Get the final result then check if the given result is correct.
        # If the result is invalid we should return an empty list.
        result = _get_part_combination(len(items), limit, limit)
        if is_this_combination_valid(result):
            return result
        else:
            return []
