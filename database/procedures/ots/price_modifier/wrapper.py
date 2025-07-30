import operator
from datetime import timedelta

from ots.offer.free_nights_offer import FreeNightsOffer
from ots.price_modifier.merged_price_modifier import MergedPriceModifier
from ots.price_modifier.price_modifier_combiner import PriceModifierCombiner
from stylers.date_helpers import get_days, datetimestr_to_datetime, datestr_to_datetime
from ots.price_modifier.price_modifier import PriceModifier


class Wrapper(object):
    def __init__(self, **keyword_parameters):
        self.plpy = keyword_parameters["plpy"]
        self.settings = keyword_parameters["settings"]
        self.productable_type = keyword_parameters["productable_type"]
        self.productable_id = keyword_parameters["productable_id"]
        self.combination_from_time = keyword_parameters["combination_from_time"]
        self.combination_to_time = keyword_parameters["combination_to_time"]
        self.price_modifiers = []

        if not keyword_parameters.get("is_merged_free_nights_enabled", False):
            # WARNING: This function also removes the non-applicable modifiers
            self._add_price_modifiers(keyword_parameters["price_modifiers"])
        else:
            # WARNING: This function also removes all merged modifiers
            self._add_merged_free_nights(keyword_parameters["price_modifiers"])

    def _add_price_modifiers(self, price_modifiers):

        for price_modifier in price_modifiers:
            if not price_modifier.is_applicable(self.productable_type, self.productable_id):
                price_modifiers.remove(price_modifier)

        for price_modifier in price_modifiers:
            copy_attributes = {
                "combination_from_time": datetimestr_to_datetime(self.combination_from_time),
                "combination_to_time": datetimestr_to_datetime(self.combination_to_time),
            }
            self.price_modifiers.append(price_modifier.copy(copy_attributes))
        self.price_modifiers.sort(key=lambda p_mod: p_mod.get_priority())
        return self

    def _add_merged_free_nights(self, price_modifiers):
        # WARNING: This function also removes all merged modifiers
        merged_price_modifier = MergedPriceModifier.factory(self.plpy, price_modifiers)
        if merged_price_modifier and merged_price_modifier.is_applicable(
            self.productable_type, self.productable_id
        ):
            copy_attributes = {
                "combination_from_time": datetimestr_to_datetime(self.combination_from_time),
                "combination_to_time": datetimestr_to_datetime(self.combination_to_time),
            }
            self.price_modifiers.append(merged_price_modifier.copy(copy_attributes))

        return self

    def _list_price_modifier_ids(self):
        return [price_modifier.get_id() for price_modifier in self.price_modifiers]

    def __str__(self):
        return (
            str(self._list_price_modifier_ids())
            + " @ "
            + str(self.combination_from_time)
            + " - "
            + str(self.combination_to_time)
        )

    @classmethod
    def _get_combinable_price_modifier_ids(cls, plpy, price_modifier_ids):
        pass

    @classmethod
    def factory(
        cls, plpy, settings, price_modifiers, from_time, to_time, productable_type=None, productable_id=None
    ):
        """
        Create combinations from available price modifiers
        """
        if not price_modifiers:
            return []

        wrappers = []

        if settings["merged_free_nights"] == "enabled":
            merged_free_night = cls(
                plpy=plpy,
                price_modifiers=price_modifiers,
                settings=settings,
                productable_type=productable_type,
                productable_id=productable_id,
                combination_from_time=from_time,
                combination_to_time=to_time,
                is_merged_free_nights_enabled=True,
            )
            if merged_free_night.price_modifiers:
                wrappers.append(merged_free_night)
        else:
            Wrapper.filter_worse_free_nights_price_modifiers(price_modifiers)

        price_modifier_ids = [price_modifier.get_id() for price_modifier in price_modifiers]
        price_modifier_combinations = cls._get_combinable_price_modifier_ids(plpy, price_modifier_ids)

        combinations = PriceModifierCombiner().combine(price_modifier_ids, price_modifier_combinations)

        date_combinations = {}
        for day in get_days(from_time, to_time):
            date_combinations[day] = {}
        for combination_index, combination in enumerate(combinations):
            for price_modifier_id in combination:
                for price_modifier in price_modifiers:
                    if price_modifier.get_id() == price_modifier_id:
                        for day in get_days(
                            price_modifier.valid_from, price_modifier.valid_to, count_nights=True
                        ):
                            if day not in date_combinations:
                                continue  # don't calculate for days not in holiday. date combinations is pre-filled
                            if combination_index not in date_combinations[day]:
                                date_combinations[day][combination_index] = []
                            if price_modifier.get_id() not in [
                                modifier.get_id() for modifier in date_combinations[day][combination_index]
                            ]:
                                date_combinations[day][combination_index].append(price_modifier)

        for day, date_combination_list in date_combinations.iteritems():
            date_combinations[day] = filter(lambda x: len(x) > 0, date_combination_list.values())
            uniques = set(tuple(day_combination) for day_combination in date_combinations[day])
            date_combinations[day] = [list(day_combination) for day_combination in uniques]

        if not date_combinations:
            return []

        combinable_price_modifiers = []
        sorted_date_combinations = sorted(date_combinations.items())
        (night_from, last_values) = sorted_date_combinations[0]
        (last_night, end_values) = sorted_date_combinations[-1]

        for day, date_combination_list in sorted_date_combinations:
            night_to = day
            period_combination = {
                "date_from": datestr_to_datetime(night_from),
                "date_to": datestr_to_datetime(night_to) + timedelta(hours=23, minutes=59, seconds=59),
                "combinations": last_values,
            }
            if date_combination_list != last_values or day == last_night:
                last_values = date_combination_list
                night_from = day
                combinable_price_modifiers.append(period_combination)

        # WARNING: if free nights are combinable with other free nights, this would be wrong
        # add extra options with removing free nights
        for period_combination in combinable_price_modifiers:
            if period_combination["combinations"]:
                for price_modifiers in period_combination["combinations"]:
                    iter_free_nights = lambda pms: (pm for pm in pms if isinstance(pm.offer, FreeNightsOffer))

                    for fn in iter_free_nights(price_modifiers):
                        # copy old option without selected fn
                        price_modifiers_new = [pm for pm in price_modifiers if pm != fn]
                        # append modified
                        period_combination["combinations"].append(price_modifiers_new)

        for period_combination in combinable_price_modifiers:
            if period_combination["combinations"]:
                for price_modifiers in period_combination["combinations"]:
                    wrappers.append(
                        cls(
                            plpy=plpy,
                            price_modifiers=price_modifiers,
                            settings=settings,
                            productable_type=productable_type,
                            productable_id=productable_id,
                            combination_from_time=period_combination["date_from"],
                            combination_to_time=period_combination["date_to"],
                        )
                    )
            else:
                # add wrappers even there are no discount. needed for price calculation
                wrappers.append(
                    cls(
                        plpy=plpy,
                        price_modifiers=[],
                        settings=settings,
                        productable_type=productable_type,
                        productable_id=productable_id,
                        combination_from_time=period_combination["date_from"],
                        combination_to_time=period_combination["date_to"],
                    )
                )

        return wrappers

    @staticmethod
    def filter_worse_free_nights_price_modifiers(price_modifiers):
        """
        Assumption from customer: the combination list (with other discounts) is the same for all all free night type discount
        so we can safely choose the best free night
        :param price_modifiers:
        :return:
        """
        combinable_price_modifiers = []

        for price_modifier in price_modifiers:
            if price_modifier.is_valid_free_night_price_modifier():
                combinable_price_modifiers.append(price_modifier)

        if not combinable_price_modifiers:
            return None

        combinable_price_modifiers.sort(key=lambda modifier: modifier.from_time, reverse=False)

        coverage_configs = []
        for price_modifier in combinable_price_modifiers:
            rate = price_modifier.offer.get_rate()
            modified_nights = price_modifier.offer.get_modified_nights()

            coverage_configs.append(
                {
                    "rate": rate,
                    "modified_nights": modified_nights,
                    "price_modifier": price_modifier,
                    "price_modifier_name": price_modifier.get_name()["en"],
                }
            )

        coverage_configs.sort(key=operator.itemgetter("modified_nights", "rate"), reverse=True)

        best_free_night_price_modifier = coverage_configs[0]["price_modifier"]

        for combinable_price_modifier in combinable_price_modifiers:
            if combinable_price_modifier != best_free_night_price_modifier:
                price_modifiers.remove(combinable_price_modifier)

        return None
