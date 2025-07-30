from datetime import datetime, timedelta


def segmented_nights(from_date1, to_date1, from_date2, to_date2, **config):
    from_time1 = datestr_to_datetime(from_date1)
    to_time1 = datestr_to_datetime(to_date1)
    from_time2 = datestr_to_datetime(from_date2)
    to_time2 = datestr_to_datetime(to_date2)


    if to_time1 is None:
        raise TypeError("segmented_nights cannot be determined because to_time1 is wrong")
    if to_time2 is None:
        raise TypeError("segmented_nights cannot be determined because to_time2 is wrong")
    if from_time1 is None:
        raise TypeError("segmented_nights cannot be determined because from_time1 is wrong")
    if from_time2 is None:
        raise TypeError("segmented_nights cannot be determined because from_time2 is wrong")

    if config.get("first_range_has_open_ending", False):
        to_time1 += timedelta(days=1)
    if config.get("second_range_has_open_ending", False):
        to_time2 += timedelta(days=1)

    latest_start = max(from_time1, from_time2)
    earliest_end = min(to_time1, to_time2)

    return max(0, (earliest_end - latest_start).days)


def datetime_to_str(datetime_in, day_only=False):
    if day_only:
        return datetime.strftime(datetime_in, "%Y-%m-%d") if type(datetime_in) != str else datetime_in[:10]
    else:
        return (
            datetime.strftime(datetime_in, "%Y-%m-%d %H:%M:%S") if type(datetime_in) != str else datetime_in
        )


def datestr_to_datetime(datestr):
    # type (...) -> Union[datetime,None]
    if datestr is None:
        return None
    if type(datestr) == datetime:
        return datestr
    try:
        return datetime.strptime(str(datestr)[:10], "%Y-%m-%d")
    except ValueError:
        return None


def datetimestr_to_datetime(datetime_or_string):
    # type (...) -> Union[datetime,None]
    if datetime_or_string is None:
        return None
    if type(datetime_or_string) == datetime:
        return datetime_or_string
    try:
        return datetime.strptime(datetime_or_string, "%Y-%m-%d %H:%M:%S")
    except ValueError:
        return datestr_to_datetime(datetime_or_string)


def month_and_day_in_range(date_to_test, act_range):
    range_from_date = datestr_to_datetime(act_range["from_time"]).date()
    range_to_date = datestr_to_datetime(act_range["to_time"]).date()
    date_to_test = datestr_to_datetime(date_to_test)

    date_in_from_time_year = date_to_test.replace(year=range_from_date.year)
    date_in_to_time_year = date_to_test.replace(year=range_to_date.year)

    return (
        range_from_date <= date_in_from_time_year.date() <= range_to_date
        or range_from_date <= date_in_to_time_year.date() <= range_to_date
    )


def get_days(from_time, to_time, **config):
    start_time = datestr_to_datetime(from_time)
    end_time = datestr_to_datetime(to_time)

    if start_time is None:
        raise TypeError("get_days cannot be ran because start_time is wrong")
    if end_time is None:
        raise TypeError("get_days cannot be ran because end_time is wrong")

    days = [
        (start_time + timedelta(days=dc)).strftime("%Y-%m-%d")
        for dc in range((end_time - start_time).days + 1)
    ]
    if config.get("count_nights", False) and len(days):
        del days[-1]

    return days


def cover_nights(uncovered_nights, date_range):
    if not uncovered_nights:
        return 0
    covered_nights = get_days(
        date_range["from_time"],
        datestr_to_datetime(date_range["to_time"]) + timedelta(days=1),
        count_nights=True,
    )
    covered_nights_count = 0
    for night in covered_nights:
        if night in uncovered_nights:
            covered_nights_count += 1
            uncovered_nights.remove(night)
    return covered_nights_count


def get_covered_number_of_nights(uncovered_nights, date_range):
    if not uncovered_nights:
        return 0
    covered_nights = get_days(
        date_range["from_time"],
        datestr_to_datetime(date_range["to_time"]) + timedelta(days=1),
        count_nights=True,
    )
    covered_nights_count = 0
    for night in covered_nights:
        if night in uncovered_nights:
            covered_nights_count += 1

    return covered_nights_count
