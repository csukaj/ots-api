class RuleCalculator:
    def __init__(self, **keyword_parameters):
        self.plpy = keyword_parameters["plpy"]
        self.subject_type = keyword_parameters["subject_type"]
        self.subject_id = keyword_parameters["subject_id"]
        self.productable_type = keyword_parameters["productable_type"]
        self.productable_id = keyword_parameters["productable_id"]
        self.price_modifiable_type = keyword_parameters["price_modifiable_type"]
        self.price_modifiable_id = keyword_parameters["price_modifiable_id"]
        self.order_itemable_index = keyword_parameters["order_itemable_index"]
        self.rule_wrappers = keyword_parameters["rule_wrappers"]
        self.settings = (keyword_parameters["settings"],)
        self.meal_plans = keyword_parameters["meal_plans"]

    def get_offer(self, room_offer):
        if type(self.rule_wrappers) is not list:
            return room_offer

        for local_wrapper in filter(None, self.rule_wrappers):
            for price_modifier in local_wrapper.price_modifiers:
                # check if price modifier is applicable for device and room_request
                if self._is_applicable(price_modifier.get_applicable_devices()):
                    calculated_modification = price_modifier.calculate(
                        room_offer,
                        self.productable_type,
                        self.productable_id,
                        self.subject_type,
                        self.subject_id,
                    )

                    if calculated_modification is not None:
                        price_modifier_info = price_modifier.get_info()

                        room_offer["discounted_price"] += calculated_modification.price
                        price_modifier_info["discount_value"] = calculated_modification.price
                        price_modifier_info["offer_summary"] = calculated_modification

                        if (
                            price_modifier_info["discount_value"] != 0
                            or price_modifier_info["offer"] == "textual"
                        ):
                            room_offer["discounts"].append(price_modifier_info)

        return room_offer

    def _is_applicable(self, applicable_rooms):
        if self.subject_type != "App\\Device":
            return True

        for room in applicable_rooms:
            if room["device_id"] == self.subject_id:
                return self.order_itemable_index in room["usage_pairs"]

        return False
