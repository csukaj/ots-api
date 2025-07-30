from itertools import islice, product

from stylers.iterator_utils import window


class BestPriceSelector:
    def __init__(self, price_option_reconnector):
        """
        :param price_option_reconnector: PriceOptionReconnector
        """
        self.price_option_reconnector = price_option_reconnector

    def select(self, price_options_per_date_ranges):
        # free nights have to be connected together
        price_options = self.price_option_reconnector.connect(price_options_per_date_ranges)

        # define iterator for valid combinations
        valid_combinations = (
            c
            for c in product(
                *[
                    [
                        {"option": option, "usages": by_date_range["usages"]}
                        for option in by_date_range["options"]
                    ]
                    for date_range, by_date_range in price_options
                ]
            )
            if self._is_valid_combination(c)
        )

        # NOTATION: the calculated price usages must be same for all date ranges in this meal plan,
        # otherwise that combination is invalid

        # search and return the minimum in valid combinations
        best_valid_combination = list(min(valid_combinations, key=self._get_sum_of_combination))

        # We got the best combination, we need to reset the modifications we made
        for idx, option in enumerate(best_valid_combination):
            best_valid_combination[idx]["option"] = self.price_option_reconnector.disconnect(option["option"])

        return best_valid_combination

    def _get_sum_of_combination(self, combination):
        discount_sum = 0

        for option in combination:
            for discount in option["option"]["discounts"]:
                # If two discounted price is same then the number of discounts matters.
                # TODO: or number of unique discounts?

                # Smaller number of discounts will be better.
                # So we shift the discounted_price with the number of discounts.
                # To ensure that this will not affect the discounted_price comparison
                # we multiply this value with a very small number.
                number_of_discounts_shift = 0.000001
                discount_sum += discount["discount_value"] + number_of_discounts_shift

        return discount_sum  # It is not necessary to add original price for minimum search

    def _is_valid_combination(self, combination):
        """
        It checks wether the combination breaks the option connectivity or not
        """
        for option_a, option_b in window((c["option"] for c in combination)):
            if not self._satisfy_connection(option_a, option_b):
                return False
        return True

    def _satisfy_connection(self, option_a, option_b):
        forward_ok = len(option_a["connected_forward"]) == 0 or (option_b in option_a["connected_forward"])
        backward_ok = len(option_b["connected_backward"]) == 0 or (option_a in option_b["connected_backward"])
        return forward_ok and backward_ok
