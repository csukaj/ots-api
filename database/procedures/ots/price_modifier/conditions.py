from datetime import datetime
from dateutil.relativedelta import relativedelta
from json import loads

from stylers.date_helpers import segmented_nights, datestr_to_datetime


def cond_booking_dates_should_be_contained(valid_from, valid_to, from_time, to_time):
    def date_to_time(date, is_ending=False):
        if type(date) == datetime:
            return (
                date.replace(hour=23, minute=59, second=59)
                if is_ending
                else date.replace(hour=0, minute=0, second=0)
            )
        if len(date) == 10:
            date += " 23:59:59" if is_ending else " 0:00:00"
        return datetime.strptime(date, "%Y-%m-%d %H:%M:%S")

    return not (
        date_to_time(valid_from) > date_to_time(from_time) or date_to_time(valid_to) < date_to_time(to_time)
    )


def cond_restricted_to_device_ids(device_id, device_ids_json):
    return device_id in loads("[" + device_ids_json + "]")


def cond_restricted_to_meal_plan_ids(
    meal_plan_id, meal_plan_ids_json
):  # not yet used, done @ price_modifier.py
    return meal_plan_id in loads("[" + meal_plan_ids_json + "]")


def cond_minimum_nights(valid_from, valid_to, from_time, to_time, minimum_nights):
    one_day_period = from_time == to_time
    nights = segmented_nights(
        valid_from,
        valid_to,
        from_time,
        to_time,
        first_range_has_open_ending=True,
        second_range_has_open_ending=one_day_period,
    )
    period_length = (to_time - from_time).days if not one_day_period else 1
    try:
        return period_length >= int(minimum_nights) and nights > 0
    except ValueError:
        return False


def cond_maximum_nights(valid_from, valid_to, from_time, to_time, maximum_nights):
    one_day_period = from_time == to_time
    nights = segmented_nights(
        valid_from,
        valid_to,
        from_time,
        to_time,
        first_range_has_open_ending=True,
        second_range_has_open_ending=one_day_period,
    )
    period_length = (to_time - from_time).days if not one_day_period else 1
    try:
        return period_length <= int(maximum_nights) and nights > 0
    except ValueError:
        return False


def cond_booking_prior_minimum_days(from_time, booking_time, booking_prior_minimum_days):
    booking_travel_day_difference = (from_time - booking_time).days
    return booking_travel_day_difference >= int(booking_prior_minimum_days)


def cond_booking_prior_maximum_days(from_time, booking_time, booking_prior_maximum_days):
    booking_travel_day_difference = (from_time - booking_time).days
    return booking_travel_day_difference <= int(booking_prior_maximum_days)


def cond_wedding_in_less_than_days(from_time, wedding_time, wedding_in_less_than_days):
    if wedding_time is None:
        return False
    wedding_travel_difference = from_time - wedding_time
    return 0 <= wedding_travel_difference.days <= int(wedding_in_less_than_days)


def cond_wedding_in_less_than_months(from_time, wedding_time, wedding_in_less_than_months):  # or equals to
    if wedding_time is None:
        return False
    return wedding_time <= from_time <= wedding_time + relativedelta(months=int(wedding_in_less_than_months))


def cond_wedding_or_anniversary_during_travel(
    from_time,
    to_time,
    wedding_time,
    anniversary_year_period,
    applicable_only_once=False,
    anniversary_year_start_from=0,
):
    if wedding_time is None:
        return False
    # wedding
    if cond_date_in_daterange(from_time, to_time, wedding_time):
        return True
    # anniversary
    anniversary_range = create_anniversary_range(
        from_time.year,
        wedding_time,
        anniversary_year_start_from,
        anniversary_year_period,
        applicable_only_once,
    )
    for year in anniversary_range:
        anniversary_time = wedding_time.replace(year)
        if cond_date_in_daterange(from_time, to_time, anniversary_time):
            return True

    return False


def cond_date_in_daterange(range_from_time, range_to_time, time_to_check):
    if time_to_check is None:
        return False
    return range_from_time.date() <= time_to_check.date() < range_to_time.date()


def cond_anniversary_in_range_days(
    booking_from_time,
    booking_to_time,
    wedding_time,
    anniversary_year_period,
    anniversary_in_range_days,
    applicable_only_once=False,
    anniversary_year_start_from=0,
):
    if wedding_time is None:
        return False

    anniversaries = create_anniversaries(
        booking_from_time,
        wedding_time,
        anniversary_year_start_from,
        anniversary_year_period,
        applicable_only_once,
    )

    for anniversary in anniversaries:
        from_time_delta = booking_from_time - anniversary
        to_time_delta = booking_to_time - anniversary

        if abs(from_time_delta.days) <= int(anniversary_in_range_days) or abs(to_time_delta.days) <= int(
            anniversary_in_range_days
        ):
            return True

    return False


def cond_anniversary_in_range_months(
    booking_from_time,
    booking_to_time,
    wedding_time,
    anniversary_year_period,
    anniversary_in_range_months,
    applicable_only_once=False,
    anniversary_year_start_from=0,
):
    if wedding_time is None:
        return False

    anniversaries = create_anniversaries(
        booking_from_time,
        wedding_time,
        anniversary_year_start_from,
        anniversary_year_period,
        applicable_only_once,
    )

    for anniversary in anniversaries:
        from_time_delta = relativedelta(booking_from_time, anniversary)
        to_time_delta = relativedelta(booking_to_time, anniversary)
        if (
            not abs(from_time_delta.years)
            and not abs(to_time_delta.years)
            and (
                abs(from_time_delta.months) < int(anniversary_in_range_months)
                or abs(to_time_delta.months) < int(anniversary_in_range_months)
            )
        ):
            return True
    return False


def cond_anniversary_in_same_month(
    booking_from_time,
    booking_to_time,
    wedding_time,
    anniversary_year_period,
    applicable_only_once=False,
    anniversary_year_start_from=0,
):
    if wedding_time is None:
        return False

    temp_time = booking_from_time
    while temp_time.year < booking_to_time.year or temp_time.month <= booking_to_time.month:
        anniversary_range = create_anniversary_range(
            temp_time.year,
            wedding_time,
            anniversary_year_start_from,
            anniversary_year_period,
            applicable_only_once,
        )

        for year in anniversary_range:
            if temp_time.year == year and temp_time.month == wedding_time.month:
                return True
        temp_time += relativedelta(months=1)

    return False


def cond_anniversary_in_same_year(
    booking_from_time,
    booking_to_time,
    wedding_time,
    anniversary_year_period,
    applicable_only_once=False,
    anniversary_year_start_from=0,
):
    if wedding_time is None:
        return False

    temp_time = booking_from_time
    while temp_time.year <= booking_to_time.year:
        anniversary_range = create_anniversary_range(
            temp_time.year,
            wedding_time,
            anniversary_year_start_from,
            anniversary_year_period,
            applicable_only_once,
        )

        for year in anniversary_range:
            if temp_time.year == year:
                return True
        temp_time += relativedelta(years=1)

    return False


def cond_room_booked_in_range_of_ages(
    age_min, age_max, room_headcount_minimum, room_headcount_maximum, usage
):
    """
    Checks if usage element's age is under or equal age, and headcount number is less than maximum
    """
    age_min = int(age_min)
    age_max = int(age_max)
    room_headcount_maximum = int(room_headcount_maximum)
    room_headcount_minimum = int(room_headcount_minimum)

    headcount = 0
    for usage_element in usage:
        if usage_element["age"] > age_max or usage_element["age"] < age_min:
            return False
        headcount += usage_element["amount"]
    if headcount > room_headcount_maximum or headcount < room_headcount_minimum:
        return False
    return True


def cond_child_room(
    age_min, age_max, room_headcount_minimum, room_headcount_maximum, nth_child_room, request
):
    """
    Return nth child room - room request indexes
    by nth_room, age, room_headcount_maximum and request
    """
    child_room_indexes = []
    for index in range(len(request)):
        if cond_room_booked_in_range_of_ages(
            age_min, age_max, room_headcount_minimum, room_headcount_maximum, request[index]["usage"]
        ):
            child_room_indexes.append(index)

    if nth_child_room is not None:
        try:
            nth_room_index = int(nth_child_room) - 1
        except ValueError:
            nth_room_index = -1
        if 0 <= nth_room_index < len(child_room_indexes):
            child_room_indexes = [child_room_indexes[nth_room_index]]
        else:
            child_room_indexes = []

    return child_room_indexes


def cond_nth_room(nth_room, indexes):
    try:
        nth_room = int(nth_room)
    except ValueError:
        nth_room = -1
    return [nth_room - 1] if nth_room - 1 in indexes else []


def cond_cart_participating_organization_ids(cart_summary, organization_ids_json, minimum_nights):
    organization_ids = loads("[" + organization_ids_json + "]")
    fitting_organization_ids_in_cart = []
    nights = 0

    if not organization_ids or cart_summary["elements"] is None or len(cart_summary["elements"]) < 2:
        return False

    for cart_element in cart_summary["elements"]:
        if cart_element["discountable_id"] in organization_ids:
            if cart_element["discountable_id"] not in fitting_organization_ids_in_cart:
                fitting_organization_ids_in_cart.append(cart_element["discountable_id"])
            interval = cart_element["interval"]
            timedelta = datetime.strptime(interval["date_to"], "%Y-%m-%d") - datetime.strptime(
                interval["date_from"], "%Y-%m-%d"
            )
            nights += timedelta.days

    return len(fitting_organization_ids_in_cart) > 1 and nights >= int(minimum_nights)


def cond_room_sharing_usage_matching(
    adult_headcount_minimum,
    adult_headcount_maximum,
    child_headcount_minimum,
    child_headcount_maximum,
    child_age_minimum,
    child_age_maximum,
    usage,
):
    adult_headcount_minimum = int(adult_headcount_minimum)
    adult_headcount_maximum = int(adult_headcount_maximum)
    child_headcount_minimum = int(child_headcount_minimum)
    child_headcount_maximum = int(child_headcount_maximum)
    child_age_maximum = int(child_age_maximum)
    child_age_minimum = int(child_age_minimum)

    if not child_age_maximum:
        return False

    adult_headcount = 0
    child_headcount = 0
    for usage_element in usage:
        if usage_element["age"] < child_age_minimum:
            return False
        if usage_element["age"] > child_age_maximum:
            adult_headcount += usage_element["amount"]
        else:
            child_headcount += usage_element["amount"]
    if adult_headcount == 0 or child_headcount == 0:  # its a different price modifier when any of these is 0
        return False
    if adult_headcount_minimum and adult_headcount < adult_headcount_minimum:
        return False
    if adult_headcount_maximum and adult_headcount > adult_headcount_maximum:
        return False
    if child_headcount_minimum and child_headcount < child_headcount_minimum:
        return False
    if child_headcount_maximum and child_headcount > child_headcount_maximum:
        return False
    return True


def cond_room_sharing(
    adult_headcount_minimum,
    adult_headcount_maximum,
    child_headcount_minimum,
    child_headcount_maximum,
    child_age_minimum,
    child_age_maximum,
    request,
    indexes,
):
    """
    Return nth child room - room request indexes
    by nth_room, age, room_headcount_maximum and request
    """

    sharing_indexes = []
    for index in indexes:
        if cond_room_sharing_usage_matching(
            adult_headcount_minimum,
            adult_headcount_maximum,
            child_headcount_minimum,
            child_headcount_maximum,
            child_age_minimum,
            child_age_maximum,
            request[index]["usage"],
        ):
            sharing_indexes.append(index)

    return sharing_indexes


def cond_booking_date_from(booking_time, booking_date_from):
    if booking_date_from is None or len(booking_date_from) != 10:
        return False
    try:
        booking_date_from = datetime.strptime(booking_date_from, "%Y-%m-%d")
    except ValueError:
        return False
    return booking_time.date() >= booking_date_from.date()


def cond_booking_date_to(booking_time, booking_date_to):
    if booking_date_to is None or len(booking_date_to) != 10:
        return False
    try:
        booking_date_to = datetime.strptime(booking_date_to, "%Y-%m-%d")
    except ValueError:
        return False
    return booking_time.date() <= booking_date_to.date()


def cond_cart_family_room_combo(
    adult_age_minimum, child_age_minimum, child_age_maximum, cart_summary, request
):
    if not is_dict_item_array_has_length(cart_summary, "elements", 2) or not is_dict_item_array_has_length(
        cart_summary, "familyComboSelections", 1
    ):
        return []

    parent_rooms = cond_child_room(adult_age_minimum, 99, 1, 99, None, request)
    child_rooms = cond_child_room(child_age_minimum, child_age_maximum, 1, 99, None, request)

    index_set = set([])

    for selection in cart_summary["familyComboSelections"]:
        if selection["child_room"] in child_rooms and selection["parent_room"] in parent_rooms:
            child_request_index = cart_summary["elements"][selection["child_room"]]["order_itemable_index"]
            parent_request_index = cart_summary["elements"][selection["parent_room"]]["order_itemable_index"]
            index_set.add(child_request_index)
            index_set.add(parent_request_index)

    return list(index_set)


def cond_suite_component_rooms(suite_component_rooms_json, cart_summary, organization_id):
    if suite_component_rooms_json:
        try:
            suite_component_rooms = loads(suite_component_rooms_json)
            if not suite_component_rooms:
                return []
        except ValueError:
            return []

    rooms_in_cart = {}

    for element in cart_summary["elements"]:
        order_itemable_name = element.get("order_itemable_name")
        if not order_itemable_name or element["discountable_id"] != organization_id:
            continue

        if order_itemable_name not in rooms_in_cart:
            rooms_in_cart[order_itemable_name] = {"amount": 0, "element_indexes": []}
        rooms_in_cart[order_itemable_name]["amount"] += element["amount"]
        rooms_in_cart[order_itemable_name]["element_indexes"].append(element["order_itemable_index"])

    indexes = []
    while _has_enough_rooms_in_cart_for_suite(suite_component_rooms, rooms_in_cart):
        indexes.extend(_pop_suite_indexes(suite_component_rooms, rooms_in_cart))

    indexes.sort()
    return indexes


def cond_group_price_modifier(
    group_headcount_minimum, group_headcount_maximum, cart_summary, price_modifiable_type, price_modifiable_id
):
    try:
        group_headcount_minimum = int(group_headcount_minimum)
        group_headcount_maximum = int(group_headcount_maximum)
    except ValueError:
        return []

    if "elements" not in cart_summary or not price_modifiable_type or not price_modifiable_id:
        return []

    headcounts = {}

    for element in cart_summary["elements"]:
        if (
            "discountable_type" in element
            and "discountable_id" in element
            and "order_itemable_index" in element
            and "usage_request" in element
            and element["discountable_id"] == price_modifiable_id
            and _is_same_class(element["discountable_type"], price_modifiable_type)
        ):
            headcount = 0
            for usage_element in element["usage_request"]:
                headcount += usage_element["amount"]
            headcounts[element["order_itemable_index"]] = headcount

    if sum(headcounts.values()) < group_headcount_minimum:
        return []

    if group_headcount_maximum == 0 or sum(headcounts.values()) <= group_headcount_maximum:
        return headcounts.keys()

    indexes = []
    traveller_headcount = 0

    for index, headcount in headcounts.iteritems():
        if traveller_headcount + headcount <= group_headcount_maximum:
            traveller_headcount += headcount
            indexes.append(index)

    return indexes


def cond_returning_client(returning_client):
    try:
        return bool(returning_client)
    except ValueError:
        return False


def _has_enough_rooms_in_cart_for_suite(suite_component_rooms, rooms_in_cart):
    if not suite_component_rooms or not rooms_in_cart:
        return False

    for room, amount in suite_component_rooms.iteritems():
        if amount < 1 or room not in rooms_in_cart or rooms_in_cart[room]["amount"] < amount:
            return False

    return True


def _pop_suite_indexes(suite_component_rooms, rooms_in_cart):
    indexes = []
    for room, amount in suite_component_rooms.iteritems():
        rooms_in_cart[room]["amount"] -= amount
        count = 0
        while count < amount:
            indexes.append(rooms_in_cart[room]["element_indexes"].pop(0))
            count += 1

    return indexes


def is_dict_item_array_has_length(dictionary, dict_key, min_length):
    return (
        dictionary is not None
        and dict_key in dictionary
        and dictionary[dict_key] is not None
        and len(dictionary[dict_key]) >= min_length
    )


def create_anniversary_range(
    booking_year, wedding_time, anniversary_year_start_from, anniversary_year_period, applicable_only_once
):
    anniversary_year_period = int(anniversary_year_period)
    anniversary_year_start_from = int(anniversary_year_start_from)
    start_year = (
        wedding_time.year + anniversary_year_period
        if (anniversary_year_start_from == 0)
        else wedding_time.year + anniversary_year_start_from
    )
    return (
        [start_year]
        if applicable_only_once
        else range(start_year, booking_year + anniversary_year_period, anniversary_year_period)
    )


def create_anniversaries(
    booking_time, wedding_time, anniversary_year_start_from, anniversary_year_period, applicable_only_once
):
    anniversary_year_period = int(anniversary_year_period)
    anniversary_year_start_from = int(anniversary_year_start_from)
    start_time = (
        wedding_time + relativedelta(years=anniversary_year_period)
        if (anniversary_year_start_from == 0)
        else wedding_time + relativedelta(years=anniversary_year_start_from)
    )

    if applicable_only_once:
        anniversaries = [start_time]
    else:
        years = range(
            start_time.year,
            (booking_time + relativedelta(years=anniversary_year_period)).year + 1,
            anniversary_year_period,
        )
        anniversaries = [wedding_time.replace(year) for year in years]
    return anniversaries


def _is_same_class(first, second):
    if first == second:
        return True
    organization_groups = ["App\\OrganizationGroup", "App\\ShipGroup"]
    if first in organization_groups and second in organization_groups:
        return True
    return False
