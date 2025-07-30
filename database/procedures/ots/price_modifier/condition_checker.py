from conditions import *


class ConditionChecker(object):
    APPLICATION_LEVEL_ROOM_REQUEST = 206
    APPLICATION_LEVEL_FULL_REQUEST = 207
    APPLICATION_LEVEL_CART = 208

    MINIMUM_NIGHTS_CHECKING_LEVEL_booking_dates_should_be_contained = 514
    MINIMUM_NIGHTS_CHECKING_LEVEL_minimum_nights_of_holiday = 515
    MINIMUM_NIGHTS_CHECKING_LEVEL_minimum_nights_in_discount_period = 516

    def __init__(self, **keyword_parameters):
        self.price_modifier = keyword_parameters["price_modifier"]

    def run(self, subject_type, subject_id, usage_pairs, override_minimum_nights=False):
        indexes = self._run_common_conditions(subject_type, subject_id, usage_pairs, override_minimum_nights)
        if not indexes:
            return []

        application_level = self.price_modifier.properties["application_level_taxonomy_id"]

        if application_level == self.APPLICATION_LEVEL_ROOM_REQUEST:
            return self._run_room_request_conditions(indexes)

        if application_level == self.APPLICATION_LEVEL_FULL_REQUEST:
            return self._run_full_request_conditions(indexes)

        if application_level == self.APPLICATION_LEVEL_CART:
            return self._run_cart_conditions(indexes)

        raise ValueError(
            "Invalid application level found for price modifier #" + str(self.price_modifier.properties["id"])
        )

    def _run_common_conditions(self, subject_type, subject_id, indexes, override_minimum_nights):
        if (
            int(self._get_meta("minimum_nights_checking_level", "0"))
            == self.MINIMUM_NIGHTS_CHECKING_LEVEL_booking_dates_should_be_contained
        ):
            if not cond_booking_dates_should_be_contained(
                self.price_modifier.from_time,
                self.price_modifier.valid_to,
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.to_time,
            ):
                return []

        if self._get_meta("restricted_to_device_ids") and subject_type == "App\\Device":
            if not cond_restricted_to_device_ids(
                subject_id, self.price_modifier.meta["restricted_to_device_ids"]
            ):
                return []

        if int(self._get_meta("minimum_nights", "0")) > 0 and not override_minimum_nights:
            if (
                int(self._get_meta("minimum_nights_checking_level", "0"))
                == self.MINIMUM_NIGHTS_CHECKING_LEVEL_minimum_nights_of_holiday
            ):
                period_from_time = self.price_modifier.abstract_search.from_time
                period_to_time = self.price_modifier.abstract_search.to_time
            else:
                period_from_time = self.price_modifier.valid_from
                period_to_time = self.price_modifier.valid_to
            if not cond_minimum_nights(
                self.price_modifier.from_time,
                self.price_modifier.to_time,
                period_from_time,
                period_to_time,
                self.price_modifier.meta["minimum_nights"],
            ):
                return []

        if int(self._get_meta("maximum_nights", "0")) > 0 and not override_minimum_nights:
            if (
                int(self._get_meta("minimum_nights_checking_level", "0"))
                == self.MINIMUM_NIGHTS_CHECKING_LEVEL_minimum_nights_of_holiday
            ):
                period_from_time = self.price_modifier.abstract_search.from_time
                period_to_time = self.price_modifier.abstract_search.to_time
            else:
                period_from_time = max(
                    self.price_modifier.abstract_search.from_time, self.price_modifier.from_time
                )
                period_to_time = min(self.price_modifier.abstract_search.to_time, self.price_modifier.to_time)
            if not cond_maximum_nights(
                self.price_modifier.from_time,
                self.price_modifier.to_time,
                period_from_time,
                period_to_time,
                self.price_modifier.meta["maximum_nights"],
            ):
                return []

        if int(self._get_meta("minimum_nights_in_accommodation", "0")) > 0:
            if not cond_minimum_nights(
                self.price_modifier.from_time,
                self.price_modifier.to_time,
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.to_time,
                self.price_modifier.meta["minimum_nights_in_accommodation"],
            ):
                return []

        if "booking_prior_minimum_days" in self.price_modifier.meta:
            if not cond_booking_prior_minimum_days(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.booking_time,
                self.price_modifier.meta["booking_prior_minimum_days"],
            ):
                return []

        if "booking_prior_maximum_days" in self.price_modifier.meta:
            if not cond_booking_prior_maximum_days(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.booking_time,
                self.price_modifier.meta["booking_prior_maximum_days"],
            ):
                return []

        if "wedding_in_less_than_days" in self.price_modifier.meta:
            if not cond_wedding_in_less_than_days(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.wedding_time,
                self.price_modifier.meta["wedding_in_less_than_days"],
            ):
                return []

        if "wedding_in_less_than_months" in self.price_modifier.meta:
            if not cond_wedding_in_less_than_months(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.wedding_time,
                self.price_modifier.meta["wedding_in_less_than_months"],
            ):
                return []

        if "wedding/anniversary_during_travel" in self.price_modifier.classification:
            if not cond_wedding_or_anniversary_during_travel(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.to_time,
                self.price_modifier.abstract_search.wedding_time,
                self._get_meta("anniversary_year_period", 1),
                "only_once" in self.price_modifier.classification,
                self._get_meta("anniversary_year_start_from", 0),
            ):
                return []

        if "anniversary_in_range_days" in self.price_modifier.meta:
            if not cond_anniversary_in_range_days(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.to_time,
                self.price_modifier.abstract_search.wedding_time,
                self._get_meta("anniversary_year_period", 1),
                self.price_modifier.meta["anniversary_in_range_days"],
                "only_once" in self.price_modifier.classification,
                self._get_meta("anniversary_year_start_from", 0),
            ):
                return []

        if "anniversary_in_range_months" in self.price_modifier.meta:
            if not cond_anniversary_in_range_months(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.to_time,
                self.price_modifier.abstract_search.wedding_time,
                self._get_meta("anniversary_year_period", 1),
                self.price_modifier.meta["anniversary_in_range_months"],
                "only_once" in self.price_modifier.classification,
                self._get_meta("anniversary_year_start_from", 0),
            ):
                return []

        if "anniversary_in_the_same_month_as_travel" in self.price_modifier.classification:
            if not cond_anniversary_in_same_month(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.to_time,
                self.price_modifier.abstract_search.wedding_time,
                self._get_meta("anniversary_year_period", 1),
                "only_once" in self.price_modifier.classification,
                self._get_meta("anniversary_year_start_from", 0),
            ):
                return []

        if "anniversary_in_the_same_year_as_travel" in self.price_modifier.classification:
            if not cond_anniversary_in_same_year(
                self.price_modifier.abstract_search.from_time,
                self.price_modifier.abstract_search.to_time,
                self.price_modifier.abstract_search.wedding_time,
                self._get_meta("anniversary_year_period", 1),
                "only_once" in self.price_modifier.classification,
                self._get_meta("anniversary_year_start_from", 0),
            ):
                return []

        if (
            "anniversary_year_start_from" in self.price_modifier.meta
            or "anniversary_year_period" in self.price_modifier.meta
        ):
            if self.price_modifier.abstract_search.wedding_time is None:
                return []

        if "booking_date_from" in self.price_modifier.meta:
            if not cond_booking_date_from(
                self.price_modifier.abstract_search.booking_time,
                self.price_modifier.meta["booking_date_from"],
            ):
                return []

        if "booking_date_to" in self.price_modifier.meta:
            if not cond_booking_date_to(
                self.price_modifier.abstract_search.booking_time, self.price_modifier.meta["booking_date_to"]
            ):
                return []

        if "returning_client" == self.price_modifier.get_condition():
            if not cond_returning_client(self.price_modifier.abstract_search.returning_client):
                return []

        return indexes

    def _run_room_request_conditions(self, indexes):
        if "nth_room" in self.price_modifier.meta:
            indexes = cond_nth_room(self.price_modifier.meta["nth_room"], indexes)

        if (
            "child_age_minimum" in self.price_modifier.meta
            or "child_age_maximum" in self.price_modifier.meta
            or "adult_headcount_minimum" in self.price_modifier.meta
            or "adult_headcount_maximum" in self.price_modifier.meta
            or "child_headcount_minimum" in self.price_modifier.meta
            or "child_headcount_maximum" in self.price_modifier.meta
        ):
            adult_headcount_minimum = self._get_meta("adult_headcount_minimum", 0)
            adult_headcount_maximum = self._get_meta("adult_headcount_maximum", 0)
            child_headcount_minimum = self._get_meta("child_headcount_minimum", 0)
            child_headcount_maximum = self._get_meta("child_headcount_maximum", 0)
            child_age_minimum = self._get_meta("child_age_minimum", 0)
            child_age_maximum = self._get_meta("child_age_maximum", 0)
            indexes = cond_room_sharing(
                adult_headcount_minimum,
                adult_headcount_maximum,
                child_headcount_minimum,
                child_headcount_maximum,
                child_age_minimum,
                child_age_maximum,
                self.price_modifier.request,
                indexes,
            )

        return indexes

    def _run_full_request_conditions(self, indexes):
        if "room_age_maximum" in self.price_modifier.meta:
            indexes = cond_child_room(
                self._get_meta("room_age_minimum", 0),
                self.price_modifier.meta["room_age_maximum"],
                self._get_meta("room_headcount_minimum", 0),
                self._get_meta("room_headcount_maximum", self.price_modifier.MAXIMUM_ROOM_SIZE),
                self._get_meta("nth_child_room", None),
                self.price_modifier.request,
            )
        return indexes

    def _run_cart_conditions(self, indexes):

        if self.price_modifier.cart_summary is None:
            return []

        if (
            "participating_organization_ids" in self.price_modifier.meta
            and "minimum_nights_in_chain" in self.price_modifier.meta
        ):
            organization_ids = self.price_modifier.meta["participating_organization_ids"]
            if organization_ids:
                organization_ids += "," + str(self.price_modifier.price_modifiable_id)

            if not cond_cart_participating_organization_ids(
                self.price_modifier.cart_summary,
                organization_ids,
                self.price_modifier.meta["minimum_nights_in_chain"],
            ):
                return []

        if "family_room_combo" == self.price_modifier.get_condition():
            indexes = cond_cart_family_room_combo(
                self._get_meta("adult_age_minimum", 0),
                self._get_meta("child_age_minimum", 0),
                self._get_meta("child_age_maximum", 0),
                self.price_modifier.cart_summary,
                self.price_modifier.request,
            )

        if "suite_component_rooms" in self.price_modifier.meta:
            indexes = cond_suite_component_rooms(
                self.price_modifier.meta["suite_component_rooms"],
                self.price_modifier.cart_summary,
                self.price_modifier.price_modifiable_id,
            )

        if "group_headcount_minimum" in self.price_modifier.meta:
            indexes = cond_group_price_modifier(
                self.price_modifier.meta["group_headcount_minimum"],
                self._get_meta("group_headcount_maximum", 0),
                self.price_modifier.cart_summary,
                self.price_modifier.price_modifiable_type,
                self.price_modifier.price_modifiable_id,
            )

        return indexes

    def _get_meta(self, name, default=None):
        return self.price_modifier.meta.get(name, default)
