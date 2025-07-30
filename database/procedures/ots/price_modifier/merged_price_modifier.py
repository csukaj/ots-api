from copy import copy

from ots.common.config import Config
from ots.offer.free_nights_offer import FreeNightsOffer
from ots.offer.offer import Offer
from ots.offer.utils.offer_summary import OfferSummary
from ots.price_modifier.abstract_price_modifier import AbstractPriceModifier
from stylers.date_helpers import segmented_nights
from ots.price_modifier.price_modifier import PriceModifier
from stylers.math_utils import MathUtils, DateInterval
from datetime import timedelta
import itertools
import functools
from stylers.date_helpers import datestr_to_datetime


class MergedPriceModifier(AbstractPriceModifier):
    """
    WARNING: Currently this class is only for merged free nights
    """

    def __init__(self, **keyword_parameters):
        super(MergedPriceModifier, self).__init__(**keyword_parameters)

        self.price_modifiers = keyword_parameters['price_modifiers']
        self.combination = keyword_parameters['combination']

        # price modifiers sorted by from time
        self.from_time = self.price_modifiers[0].offer.from_time
        to_times = [pm.offer.to_time for pm in self.price_modifiers]
        self.to_time = max(to_times)

        self.properties = {
            'modifier_type_taxonomy_id': Config.PRICE_MODIFIER_TYPE_DISCOUNT
        }

    def __str__(self):
        return str(
            {
                'name': self.get_name(),
                'from_time': str(self.from_time),
                'to_time': str(self.to_time),
                'price_modifiable_type': self.price_modifiable_type,
                'price_modifiable_id': self.price_modifiable_id
            }
        )

    def get_id(self):
        return ','.join([str(price_modifier.properties['id']) for price_modifier in self.price_modifiers])

    def get_name(self):
        return {
            'en': 'Merged Free Nights: ' +
                  ', '.join([price_modifier.get_name()['en'] for price_modifier in self.price_modifiers])
        }

    def get_priority(self):
        priorities = [price_modifier.properties['priority'] for price_modifier in self.price_modifiers]
        return str(min(priorities))

    def calculate(self, room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation=True,
                  all_price_modifiers_in_combination=None):
        meal_plan_id = room_offer['meal_plan_id']
        price = 0
        free_nights = []
        for pm_id, config in self.combination.iteritems():
            # The get_n_free_nights function does not set these parameters, so we have to call the super
            # Unfortunately the offer#calculate is a mutating function
            Offer.calculate(
                config['price_modifier'].offer,
                room_offer,
                productable_type,
                productable_id,
                subject_type,
                subject_id,
                switch_calculation
            )

            free_nights = config['price_modifier'].offer.get_n_free_nights(
                config['discounted'],
                meal_plan_id,
                extend=free_nights, # to be known which nights are already free (don't give the same)
                full_range=True
            )

        return OfferSummary(free_nights)

    def is_applicable(self, productable_type, productable_id):
        if self.price_modifiable_type == 'App\\Organization' or self.price_modifiable_type == 'App\\Cruise':
            return len(self.get_applicable_devices()) > 0
        elif self.price_modifiable_type == 'App\\ShipGroup':
            raise NotImplementedError
        else:
            raise ValueError

    def get_applicable_devices(self):
        common_applicable_rooms = None

        for price_modifier in self.price_modifiers:
            applicable_rooms = price_modifier.get_applicable_devices(True)
            if common_applicable_rooms is None:
                common_applicable_rooms = applicable_rooms
            else:
                for common_applicable_room in common_applicable_rooms:
                    found = False
                    for applicable_room in applicable_rooms:
                        if applicable_room['device_id'] == common_applicable_room['device_id']:
                            found = True
                            break
                    if not found:
                        common_applicable_rooms.remove(common_applicable_room)

        return common_applicable_rooms

    def get_info(self):
        return {
            'id': self.get_id(),
            'name': self.get_name(),
            'description': self.get_description(),
            'modifier_type': self.get_modifier_type(),
            'condition': self.get_condition(),
            'offer': self.get_offer(),
            'period': {
                'date_from': self.request_from_time.strftime('%Y-%m-%d'),
                'date_to': self.request_to_time.strftime('%Y-%m-%d')
            }
        }

    def get_application_type(self):
        return self._get_taxonomy_name(self.properties['application_level_taxonomy_id'])

    def get_modifier_type(self):
        return self.properties['modifier_type_taxonomy_id']

    def get_condition(self):
        return 'merged_free_nights'

    def get_offer(self):
        return 'merged_free_nights'

    def get_description(self):
        description = {
            'en': ''
        }
        for price_modifier in self.price_modifiers:
            price_modifier_description = price_modifier.get_description()
            if price_modifier_description:
                description['en'] += price_modifier_description['en'] + ', '
        description['en'] = description['en'][:-2]

        return description

    @staticmethod
    def factory(plpy, price_modifiers):

        free_nights = filter(lambda pm: pm.is_valid_free_night_price_modifier(), price_modifiers)
        optimum = MergedPriceModifier._get_optimum(free_nights)

        if MergedPriceModifier._is_part_optimum_merged(optimum):
            for price_modifier in free_nights:
                price_modifiers.remove(price_modifier)
        else:
            return None

        sorted_optimum = optimum['combination'].items()
        sorted_optimum.sort(
            key=lambda (modifier_id, config): config['price_modifier'].from_time,
            reverse=False
        )


        first_price_modifier = sorted_optimum[0][1]['price_modifier']

        return MergedPriceModifier(
            plpy=plpy,
            request=first_price_modifier.request,
            request_from_time=first_price_modifier.request_from_time,
            request_to_time=first_price_modifier.request_to_time,
            date_ranges=first_price_modifier.date_ranges,
            price_modifiable_type=first_price_modifier.price_modifiable_type,
            price_modifiable_id=first_price_modifier.price_modifiable_id,
            price_modifiers=[config['price_modifier'] for pm_id, config in sorted_optimum],
            combination=optimum['combination'],
            available_devices=None
        )

    def copy(self, attributes=None):
        if attributes is None:
            attributes = {}
        for key, value in self.keyword_parameters.iteritems():
            if key not in attributes:
                attributes[key] = value

        return MergedPriceModifier(**attributes)

    @staticmethod
    def _get_coverage(interval, free_night):

        valid_through_days = len(interval)
        cumulation_maximum = free_night.offer.get_cumulation_maximum()
        cumulation_frequency = free_night.offer.get_cumulation_frequency()
        number_of_free_nights = free_night.offer.get_discounted_nights()

        applied_times = min(cumulation_maximum, valid_through_days // cumulation_frequency)
        discounted_days = applied_times * number_of_free_nights
        covered_days = applied_times * cumulation_frequency
        uncovered_days = valid_through_days - covered_days

        return {
            'discounted_nights': discounted_days,
            'uncovered_nights': uncovered_days,
            'remaining_cumulation': cumulation_maximum - applied_times
        }

    @classmethod
    def _will_this_satisfy_stricter_period_rule(cls, combination, this_item, coverages):
        # get this item frequency, discounted_nights, uncovered_nights
        # calculate strictness (7=6 is stricter than 7=5)
        # add less strict period's uncovered nights together
        # if uncovered sum is bigger or equal to this item's frequency then True, otherwise False

        # discount / frequency
        _calculate_strictness = lambda discount, frequency : (frequency-discount) / frequency

        # the bigger is the more stricter
        this_item_strictness = _calculate_strictness(this_item[2], this_item[1])

        uncovered_sum = 0

        for fn, coverage in coverages:
            frequency = fn.offer.get_cumulation_frequency()
            discount = fn.offer.get_discounted_nights()
            strictness = _calculate_strictness(discount, frequency)

            if strictness <= this_item_strictness:
                uncovered_sum += coverage['uncovered_nights']


        for qty, item in combination:
            strictness = _calculate_strictness(item[2], item[1])
            if strictness <= this_item_strictness:
                uncovered_sum -= item[0].offer.get_cumulation_frequency()

        if uncovered_sum >= this_item[1]:
            return True

        return False

    @classmethod
    def _is_this_satisfy_stricter_period_rule(cls, combination, coverages):
        # since every step will satisfy the stricter_period_rule, the final result will also satisfy this
        return True

    @classmethod
    def _get_optimum_for_intervals(cls, free_night_intervals, free_nights):
        # assuming that free_night_intervals are exclusive sets

        coverages = map(
            lambda (interval, free_night): MergedPriceModifier._get_coverage(interval, free_night),
            zip(free_night_intervals, free_nights)
        ) # [{'discounted_nights': ..., 'uncovered_nights': ...,}, {'discounted_nights': ..., 'uncovered_nights': ...,}]

        coverage_sum = functools.reduce(
            lambda x, acc: {
                'discounted_nights': x['discounted_nights'] + acc['discounted_nights'],
                'uncovered_nights': x['uncovered_nights'] + acc['uncovered_nights']
            },
            coverages,
            { 'discounted_nights': 0, 'uncovered_nights': 0 }
        )

        frequencies = map(lambda x: x.offer.get_cumulation_frequency(), free_nights)

        # discounted nights / frequency
        discount_nights = map(lambda x: x.offer.get_discounted_nights(), free_nights)

        remaining_cumulations = map(lambda x: x['remaining_cumulation'], coverages)

        fn_coverage_tuples = [ (fn, coverage) for fn, coverage in zip(free_nights, coverages) ]

        # [(times, (fn, freq, discount)), (times, (fn, freq, discount)), ... ] iterable, but not a list
        optimal_combination =  list(MathUtils.knap_sack(
            zip(free_nights, frequencies, discount_nights, remaining_cumulations),
            coverage_sum['uncovered_nights'],
            give_bigger_when_the_value_is_same=True,
            can_this_item_be_added= lambda combination, item: cls._will_this_satisfy_stricter_period_rule(combination, item, fn_coverage_tuples),
            is_this_combination_valid= lambda combination: cls._is_this_satisfy_stricter_period_rule(combination, fn_coverage_tuples)
        ))

        there_are_unused_free_nights = MergedPriceModifier._are_there_any_unused_free_nights(
            coverage_sum['uncovered_nights'],
            optimal_combination,
            free_nights,
            free_night_intervals
        )

        if there_are_unused_free_nights:
            return {'combination':{}, 'discounted_nights':0} # Yes it is optimal but we dont merge unused free nights

        # calculate overall free night sum

        get_free_night = lambda grp: grp[0]
        get_frequency = lambda grp: grp[1]
        get_discount = lambda grp: grp[2]

        overall_free_nights = {
            get_free_night(grp).get_id(): {
                'price_modifier': get_free_night(grp),
                'times': times,
                'frequency': get_frequency(grp),
                'discount': get_discount(grp),
                'discounted': get_discount(grp)*times,
                'merged': True
            } for times,grp in optimal_combination
        }

        for fn, coverage, discount, frequency in zip(free_nights, coverages, discount_nights, frequencies):
            # if the additional
            overall_free_nights.setdefault(fn.get_id(), {
                'price_modifier': fn,
                'times': 0,
                'frequency': frequency,
                'discount': discount,
                'discounted': 0,
                'merged': False
            })
            overall_free_nights[fn.get_id()]['times'] += coverage['discounted_nights'] / discount
            overall_free_nights[fn.get_id()]['discounted'] += coverage['discounted_nights']



        coverage_sum['discounted_nights'] += sum(get_discount(grp) * times for times, grp in optimal_combination)
        coverage_sum['uncovered_nights'] -= sum(get_frequency(grp) * times for times, grp in optimal_combination)

        return {
            'combination' : overall_free_nights,
            'discounted_nights': sum(config['discounted'] for fn, config in overall_free_nights.iteritems())
        }

    @staticmethod
    def _is_part_optimum_merged(optimum):
        # free nights are not combinable with each other
        # but with merged free night it is possible to combine them
        # there is no need to give an additional free night from uncovered nights
        # it is enough to be more the one free night in the optimum
        return len(optimum['combination']) > 1


    @staticmethod
    def _get_optimum(free_nights):

        best_merged_free_night = {'combination': {}, 'discounted_nights':0}

        # an example of free_nights: [fn1,fn2,fn3]
        if len(free_nights) > 0:
            request_interval = DateInterval(
                from_date=datestr_to_datetime(free_nights[0].request_from_time),
                to_date=datestr_to_datetime(free_nights[0].request_to_time) - timedelta(hours=23, minutes=59, seconds=59),
                from_open=False,
                to_open=True
            )

        # an example of possible_merged_free_nights: [
        #   [fn1,fn2],
        #   [fn1,fn3],
        #   [fn2,fn3],
        #   [fn1,fn2,fn3]
        # ]
        possible_merged_free_nights = MathUtils.powerset_from_2_elements(free_nights)

        # an example of possible_merged_free_night: [fn1,fn2,fn3]
        for possible_free_night_combination in reversed(list(possible_merged_free_nights)):

            # This "if" is not required but it helps to run the code faster
            #   if the next combination contains less elements compared to the stored best
            #   then we know the stored best is the overall best
            if best_merged_free_night['discounted_nights'] > 0 and len(possible_free_night_combination) < len(best_merged_free_night['combination']) :
                return best_merged_free_night

            # an example of date_ranges: [[dr1,dr2],[dr3,dr4],[dr1,dr5,dr6]] because of [fn1,fn2,fn3]
            date_ranges = map(
                lambda free_night: filter(
                    lambda date_range: free_night.get_id() in date_range['price_modifier_ids'],
                    free_night.date_ranges[Config.DATE_RANGE_TYPE_PRICE_MODIFIER]
                ),
                possible_free_night_combination
            )

            # e.g [(dr1,dr3,dr1), (dr1,dr3,dr5)]
            date_range_combinations = itertools.product(*date_ranges)

            # we calc intersection with the request interval, because we only want to see actual date_ranges
            # an example of possible_date_range_coverages: [(int1,int3,int1),(int1,int3,int5), ...]
            possible_date_range_coverages = map(
                lambda date_range_combination: map(
                    lambda date_range: DateInterval.intersection(
                        DateInterval(
                            datestr_to_datetime(date_range['from_time']),
                            datestr_to_datetime(date_range['to_time']) ,
                            from_open=False,
                            to_open=False
                        ),
                        request_interval
                    ),
                    date_range_combination
                ),
                date_range_combinations
            )

            # we filter out empty date ranges
            # empty date ranges can occur because of the intersection calculation
            possible_date_range_coverages = map(
                lambda date_range_combination: filter(
                    lambda date_range: len(date_range) > 0,
                    date_range_combination
                ),
                possible_date_range_coverages
            )

            # an example of exclusivity_checks = [(1,False),(2,True),(3,False)]
            exclusivity_checks = map(
                lambda (key, intervals): (key, DateInterval.exclusives(*intervals)),
                enumerate(possible_date_range_coverages)
            )

            # an example of empty intersections = [(2,int2),(3,int3)] where int1 and int2 is empty
            exclusives = filter(
                lambda (key,exclusive): exclusive,
                exclusivity_checks
            )

            # an example of empty_intersection: (2,int2)
            for key, exclusive in exclusives:

                # e.g (int1,int3,int5)
                possible_date_range_coverage = possible_date_range_coverages[key]

                part_optimum = MergedPriceModifier._get_optimum_for_intervals(
                    possible_date_range_coverage,
                    possible_free_night_combination
                )

                # update the best if we found better
                if part_optimum['discounted_nights'] > best_merged_free_night['discounted_nights']:
                    best_merged_free_night = part_optimum

        return best_merged_free_night

    @staticmethod
    def _are_there_any_unused_free_nights(sum_of_uncovereds, optimal_combination, free_nights, free_night_intervals):

        # Not necessarily all uncovered nights will be covered by merging free nights.
        # If the remaining uncovered nights is bigger than the smallest FN interval,
        # then the smallest FN is not used.

        # But if the smallest FN has more periods it is possible to use it in the first period
        # and don't use it in the second period
        # This case, the second period is unused, but the free night is used

        # 5=4 6=5 7=6 5=4
        # The two 5=4 are the same free nights in different periods.
        # 5=4 6=5 7=6 are merged together
        # so 5=4 is used

        # But optimal combination will only have the 7=6

        covered_by_merge = sum([t * grp[1] for t, grp in optimal_combination])
        remaining_uncovereds = sum_of_uncovereds - covered_by_merge

        intervals_per_free_nights = {}
        for free_night, interval in zip(free_nights, free_night_intervals):
            intervals_per_free_nights.setdefault(free_night.get_id(), []).append(interval)

        for free_night, intervals in intervals_per_free_nights.iteritems():
            all_unused = True
            for interval in intervals:
                if len(interval) > remaining_uncovereds:
                    all_unused = False

            if all_unused:
                return True

        return False

