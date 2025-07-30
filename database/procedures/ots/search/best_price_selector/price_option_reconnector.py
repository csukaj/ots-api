from stylers.date_helpers import datetimestr_to_datetime
from itertools import product
from stylers.iterator_utils import window


class PriceOptionReconnector:
    """
    Some price options like those that have free nights
    can not be separated into wrapper ranges.
    This class reconnects the separated parts.
    """

    def connect(self, price_options_per_date_ranges):
        # The option is enough to be connected to the neighbours
        options = list(price_options_per_date_ranges)
        for ((date_range1, option_a), (date_range2, option_b)) in self._all_possible_option_pairs(options):
            option_a.setdefault("connected_forward", [])
            option_a.setdefault("connected_backward", [])
            option_b.setdefault("connected_forward", [])
            option_b.setdefault("connected_backward", [])
            if self._is_connectable(
                option_a, option_b, self._parse_range(date_range1), self._parse_range(date_range2)
            ):
                option_a["connected_forward"].insert(0, option_b)
                option_b["connected_backward"].insert(0, option_a)

        return options

    def disconnect(self, option):

        try:
            del option["connected_forward"]
            del option["connected_backward"]
        except:
            pass

        return option

    def _parse_range(self, str_date_range):
        splitted_range = str_date_range.split("00:00:00-")

        # get dates in strings
        from_date = splitted_range[0]
        to_date = splitted_range[1].split()[0]

        return {"from": datetimestr_to_datetime(from_date), "to": datetimestr_to_datetime(to_date)}

    def _is_connectable(self, A, B, rangeA, rangeB):
        # currently only free nights are connectable
        a_fn_ids = {
            d["id"]
            for d in A["discounts"]
            if d["offer"] == "free_nights"
            and self._range_contains_this_free_night(rangeA["from"], rangeA["to"], d)
        }
        b_fn_ids = {
            d["id"]
            for d in B["discounts"]
            if d["offer"] == "free_nights"
            and self._range_contains_this_free_night(rangeB["from"], rangeB["to"], d)
        }

        return a_fn_ids.intersection(b_fn_ids)

    def _all_possible_option_pairs(self, options):
        return (
            ((date_range1, option1), (date_range2, option2))
            for (date_range1, date_range1_options), (date_range2, date_range2_options) in window(options)
            for option1, option2 in product(date_range1_options["options"], date_range2_options["options"])
        )

    def _range_contains_this_free_night(self, range_from, range_to, discount):
        fn_from = datetimestr_to_datetime(discount["period"]["date_from"])
        fn_to = datetimestr_to_datetime(discount["period"]["date_to"])
        range_contains_this_free_night = fn_from <= range_from <= range_to <= fn_to
        return range_contains_this_free_night
