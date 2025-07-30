from copy import deepcopy
from datetime import timedelta
from ots.repository.model.price import Price
from ots.repository.model.price_row_model import PriceRowModel
from ots.repository.model.price_row_collection_model import PriceRowCollectionModel
from ots.offer.offer import Offer
from ots.common.config import Config
from ots.offer.utils.date_range_price_description import DatePriceDescription
from ots.offer.utils.offer_summary import OfferSummary
from stylers.date_helpers import datetimestr_to_datetime, datestr_to_datetime
from stylers.math_utils import DateInterval
from typing import Optional


class FreeNightsOffer(Offer):
    """
    Free night offer calculating
    """

    def __init__(self, **keyword_parameters):
        super(FreeNightsOffer, self).__init__(**keyword_parameters)

    def _is_applicable_date_range(self, date_range):
        """
        In free nights offer when we get the applicable offers we don't want the +1 day at the offer's end date
        """
        offer_from = self.from_time
        offer_to = self.to_time - timedelta(hours=47, minutes=59, seconds=59)

        is_date_range_started_during_the_offer = (
            offer_from <= datetimestr_to_datetime(date_range["from_time"]) <= offer_to
        )
        is_offer_started_in_the_date_range = (
            datetimestr_to_datetime(date_range["from_time"])
            <= offer_from
            <= datetimestr_to_datetime(date_range["to_time"])
        )

        return is_date_range_started_during_the_offer or is_offer_started_in_the_date_range

    def calculate(
        self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
    ):

        Offer.calculate(
            self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
        )

        if self.is_meta_empty("discounted_nights"):
            return OfferSummary([])

        n = self.get_modified_nights()

        free_nights = list(self.get_n_free_nights(n, int(room_offer["meal_plan_id"])))
        return OfferSummary(free_nights)

    def get_n_free_nights(self, n, meal_plan_id, extend=(), full_range=False):
        """
        :param n:
        :param meal_plan_id:
        :param extend: list of DatePriceDescriptions, if specified this means "give n additional nights except these"
        :param full_range:
        :return:
        """

        deduction_base_prices = self.get_deduction_base_prices()
        meal_plan_id = self.meta.get("deduction_base_meal_plan_id", meal_plan_id)

        request_interval = self._get_request_interval()

        applicable_open_ranges = self._get_applicable_open_date_ranges()
        applicable_open_ranges = self._get_shrunk_date_ranges_to_request(
            request_interval, applicable_open_ranges
        )

        if "use_last_consecutive_night" in self.classification:
            free_night_getting_method = self._get_last_n_nights
        else:
            free_night_getting_method = self._get_first_n_cheapest_price

        return free_night_getting_method(
            n, meal_plan_id, deduction_base_prices, extend, applicable_open_ranges
        )

    def _get_request_interval(self):
        request_interval = DateInterval(
            from_date=datestr_to_datetime(self.price_modifier.request_from_time),
            to_date=datestr_to_datetime(self.price_modifier.request_to_time)
            - timedelta(hours=23, minutes=59, seconds=59),
            from_open=False,
            to_open=True,
        )
        return request_interval

    def _get_first_n_cheapest_price(
        self, n, meal_plan_id, deduction_base_prices, extend, applicable_open_ranges
    ):
        # type: (int, int, dict, List[DatePriceDescription], List[dict]) -> Generator[DatePriceDescription]
        date_range_prices = []

        for date_range in applicable_open_ranges:
            start_time = self._get_start_time_for_nightly_price(date_range)

            price = self._get_price_of_date_range_and_meal_plan(
                {Config.DATE_RANGE_TYPE_OPEN: [date_range], Config.DATE_RANGE_TYPE_PRICE_MODIFIER: None},
                meal_plan_id,
                deduction_base_prices,
                start_time,
                start_time + timedelta(hours=47, minutes=59, seconds=59),  # for one night price
                None,
            )  # type : PriceRowCollectionModel

            if price is not None:
                date_range_prices.append({"date_range": date_range, "price": price})

        # the dates should be given from the end of the date_range
        sorted_date_range_prices = reversed(
            sorted(date_range_prices, key=lambda fn: datetimestr_to_datetime(fn["date_range"]["from_time"]))
        )
        # the dates should be given from the cheapest dates first
        sorted_date_range_prices = sorted(sorted_date_range_prices, key=lambda fn: fn["price"].summary.net)

        # we should return n free nights
        n_counter = n
        for date_range_price in sorted_date_range_prices:
            for date in self._get_dates_in_date_range_reversed(date_range_price["date_range"]):
                if n_counter > 0:
                    try:
                        find_same_date_price_in_extending_free_night = (e for e in extend if e.date == date)
                        free_night = find_same_date_price_in_extending_free_night.next()
                    except:
                        # only newly given free night counts
                        n_counter -= 1
                        free_night = DatePriceDescription(
                            date=date,
                            price=-self.get_calculations_base_price(date_range_price["price"].summary),
                            price_row_collection=-date_range_price["price"],
                        )

                    yield free_night

    def _get_start_time_for_nightly_price(self, date_range):
        date_range_from_time = date_range["from_time"]
        date_range_to_time = date_range["to_time"]
        start_time = date_range_from_time
        if (
            date_range_from_time < self.get_max_from() < date_range_to_time
        ):  # price calculator needs to have segmented_nights to calculate
            start_time = self.get_max_from()
        return start_time

    def get_rate(self):
        return float(self.meta["discounted_nights"]) / float(self.meta["cumulation_frequency"])

    def get_modified_nights(self):
        return FreeNightsOffer.get_modified_nights_config(
            float(self.meta["discounted_nights"]),
            float(self.meta["cumulation_frequency"]) if "cumulation_frequency" in self.meta else None,
            self.get_validity_nights(),
            int(self.meta["cumulation_maximum"]) if "cumulation_maximum" in self.meta else None,
        )["discounted_nights"]

    def get_cumulation_maximum(self):
        return int(self.meta.get("cumulation_maximum", 9999))

    def get_cumulation_frequency(self):
        return int(self.meta.get("cumulation_frequency", 0))

    def get_cumulations(self):
        return min(
            float(self.get_validity_nights()) // float(self.meta["cumulation_frequency"]),
            self.get_cumulation_maximum(),
        )

    def get_covered_nights(self):
        return self.get_cumulations() * float(self.meta["cumulation_frequency"])

    def get_discounted_nights(self):
        return float(self.meta["discounted_nights"])

    def get_remaining_cumulations(self):
        return self.get_cumulation_maximum() - self.get_cumulations()

    def _get_last_n_nights(self, n, meal_plan_id, deduction_base_prices, extend, applicable_open_ranges):
        n_counter = n
        for open_date_range in reversed(applicable_open_ranges):
            from_datetime = datetimestr_to_datetime(open_date_range["from_time"])
            to_datetime = from_datetime + timedelta(hours=47, minutes=59, seconds=59)

            # get price of this date range
            price = super(FreeNightsOffer, self)._get_price_of_date_range_and_meal_plan(
                {Config.DATE_RANGE_TYPE_OPEN: [open_date_range], Config.DATE_RANGE_TYPE_PRICE_MODIFIER: None},
                meal_plan_id,
                deduction_base_prices,
                from_datetime,
                to_datetime,
                None,
            )  # type: Optional[PriceRowCollectionModel]

            if price is not None:
                for date in self._get_dates_in_date_range_reversed(open_date_range):
                    if n_counter > 0:
                        try:
                            find_same_date_price_in_extending_free_night = (
                                e for e in extend if e.date == date
                            )
                            free_night = find_same_date_price_in_extending_free_night.next()
                        except:
                            # only newly given free night counts
                            n_counter -= 1
                            free_night = DatePriceDescription(
                                date=date,
                                price=-self.get_calculations_base_price(price.summary),
                                price_row_collection=-price,
                            )

                        yield free_night

    def _get_length_of_date_range(self, date_range):
        from_datetime = date_range["from_time"]
        end_of_date_range = date_range["to_time"]

        length = (end_of_date_range - from_datetime).days + 1

        return length

    @staticmethod
    def get_modified_nights_config(modified_nights, cumulation_frequency, nights, cumulation_maximum=None):
        actual_cumulation = 1
        covered_nights = cumulation_frequency
        if cumulation_frequency:
            actual_cumulation = float(nights) // float(cumulation_frequency)
            if cumulation_maximum:
                actual_cumulation = min(actual_cumulation, cumulation_maximum)
            covered_nights = actual_cumulation * cumulation_frequency

        return {
            "discounted_nights": actual_cumulation * modified_nights,
            "covered_nights": covered_nights,
            "cumulations": actual_cumulation,
        }

    def _get_shrunk_date_ranges_to_request(self, request_interval, date_ranges):
        def get_shrunk_date_range(date_range):
            from_time = datestr_to_datetime(date_range["from_time"])
            to_time = datestr_to_datetime(date_range["to_time"])

            from_time = from_time if request_interval.from_date < from_time else request_interval.from_date
            to_time = to_time if request_interval.to_date > to_time else request_interval.to_date

            if to_time is None:
                raise TypeError("free night cannot be determined because to_time is wrong")

            if to_time == request_interval.to_date and request_interval.to_open:
                # request interval is open so it does not contain this last night
                to_time -= timedelta(days=1)

            date_range_copy = deepcopy(date_range)
            date_range_copy["from_time"] = from_time
            date_range_copy["to_time"] = to_time

            return date_range_copy

        return map(get_shrunk_date_range, date_ranges)

    def _get_applicable_open_date_ranges(self):
        open_ranges = self.date_ranges[Config.DATE_RANGE_TYPE_OPEN]
        pm_ranges = self.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER]

        # convert date strings to datetime
        for range in open_ranges + pm_ranges:
            range["from_time"] = datestr_to_datetime(range["from_time"])
            range["to_time"] = datestr_to_datetime(range["to_time"])

        # get open ranges which have intersection with price modifier ranges
        intersecting_date_ranges = []
        for open_range in open_ranges:
            for pm_range in pm_ranges:
                pmr_starts_in_or = open_range["from_time"] <= pm_range["from_time"] <= open_range["to_time"]
                or_start_in_pmr = pm_range["from_time"] <= open_range["from_time"] <= pm_range["to_time"]
                if pmr_starts_in_or or or_start_in_pmr:
                    intersecting_date_ranges.append(open_range)
                    break

        return intersecting_date_ranges  # sorted date_ranges by date

    def _get_dates_in_date_range_reversed(self, date_range):
        from_time = datestr_to_datetime(date_range["from_time"])
        to_time = datestr_to_datetime(date_range["to_time"])

        if from_time is None:
            raise TypeError("free night cannot be determined because from_time is wrong")
        if to_time is None:
            raise TypeError("free night cannot be determined because to_time is wrong")

        time_difference = to_time - from_time

        # the "+1" is because the interval is closed
        days = time_difference.days + 1

        for shift in range(days):
            yield to_time - timedelta(days=shift)
