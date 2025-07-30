from copy import deepcopy
from importlib import import_module
from json import loads

from ots.offer.utils.offer_summary import OfferSummary
from ots.price_modifier.abstract_price_modifier import AbstractPriceModifier
from ots.price_modifier.condition_checker import ConditionChecker
from stylers.utils import execute_cached_query, underscore_to_camelcase


class PriceModifier(AbstractPriceModifier):
    MAXIMUM_ROOM_SIZE = 100
    DEFAULT_ROOM_COUNT = 2

    def __init__(self, **keyword_parameters):
        super(PriceModifier, self).__init__(**keyword_parameters)
        self.keyword_parameters = keyword_parameters
        self.properties = keyword_parameters["properties"]
        self.from_time = keyword_parameters["from_time"]
        self.to_time = keyword_parameters["to_time"]
        self.abstract_search = keyword_parameters.get("abstract_search")
        self.age_resolver = keyword_parameters.get("age_resolver")
        self.cart_summary = keyword_parameters.get("cart_summary")

        self.all_price_modifiers_in_combination = None

        self.valid_from = max(self.request_from_time, self.from_time)
        self.valid_to = min(self.request_to_time, self.to_time)

        self._load_classification()
        self._load_meta()
        self._load_offer()

    def __str__(self):
        return str(
            {
                "name": self.get_name(),
                "from_time": str(self.from_time),
                "to_time": str(self.to_time),
                "valid_from": str(self.valid_from),
                "valid_to": str(self.valid_to),
                "combination_from_time": str(self.combination_from_time),
                "combination_to_time": str(self.combination_to_time),
                "price_modifiable_type": self.price_modifiable_type,
                "price_modifiable_id": self.price_modifiable_id,
                "properties": self.properties,
                "classification": self.classification,
                "meta": self.meta,
            }
        )

    def get_id(self):
        return self.properties["id"]

    def get_name(self):
        return self._get_description_translations(self.properties["name_description_id"])

    def get_priority(self):
        return self.properties["priority"]

    def calculate(
        self,
        room_offer,
        productable_type,
        productable_id,
        subject_type,
        subject_id,
        switch_calculation=True,
        all_price_modifiers_in_combination=None,
    ):
        """
        :returns: OfferSummary or None
        """

        self.all_price_modifiers_in_combination = all_price_modifiers_in_combination
        applicable_meal_plans = self._get_applicable_meal_plans()
        if applicable_meal_plans and int(room_offer["meal_plan_id"]) not in applicable_meal_plans:
            return None

        if productable_type == "App\\Device":
            for applicable_device in self.get_applicable_devices():
                if (
                    applicable_device["device_id"] == productable_id
                    and room_offer["order_itemable_index"] in applicable_device["usage_pairs"]
                ):
                    return self.offer.calculate(
                        room_offer,
                        productable_type,
                        productable_id,
                        subject_type,
                        subject_id,
                        switch_calculation,
                    )
        else:
            return self.offer.calculate(
                room_offer, productable_type, productable_id, subject_type, subject_id, switch_calculation
            )

        return OfferSummary([])

    def is_applicable(self, productable_type, productable_id):
        if self.price_modifiable_type == "App\\Organization" or self.price_modifiable_type == "App\\Cruise":
            return len(self.get_applicable_devices()) > 0
        elif self.price_modifiable_type == "App\\ShipGroup":
            return len(ConditionChecker(price_modifier=self).run(productable_type, productable_id, [0])) > 0
        else:
            raise ValueError

    def get_applicable_devices(self, override_minimum_nights=False):
        """
        Iterate available devices and check if applicable (conditions)
        """
        available_devices = deepcopy(self.available_devices)
        applicable_devices = []
        for available_device in available_devices:
            available_device["usage_pairs"] = ConditionChecker(price_modifier=self).run(
                "App\\Device",
                available_device["device_id"],
                available_device["usage_pairs"],
                override_minimum_nights,
            )
            if available_device["usage_pairs"]:
                applicable_devices.append(available_device)

        return applicable_devices

    def get_info(self):
        return {
            "id": self.get_id(),
            "name": self.get_name(),
            "description": self.get_description(),
            "modifier_type": self.get_modifier_type(),
            "condition": self.get_condition(),
            "offer": self.get_offer(),
            "period": self.get_period(),
        }

    def get_application_type(self):
        return self._get_taxonomy_name(self.properties["application_level_taxonomy_id"])

    def get_modifier_type(self):
        return self.properties["modifier_type_taxonomy_id"]

    def get_condition(self):
        return self._get_taxonomy_name(self.properties["condition_taxonomy_id"])

    def get_offer(self):
        return self._get_taxonomy_name(self.properties["offer_taxonomy_id"])

    def get_description(self):
        return self._get_description_translations(self.properties["description_description_id"])

    def get_period(self):
        if self.get_offer() != "free_nights":
            return {
                "date_from": self.combination_from_time.strftime("%Y-%m-%d"),
                "date_to": self.combination_to_time.strftime("%Y-%m-%d"),
            }
        else:
            return {
                "date_from": self.valid_from.strftime("%Y-%m-%d"),
                "date_to": self.valid_to.strftime("%Y-%m-%d"),
            }

    @staticmethod
    def get_all_combinations(plpy, price_modifier_ids):
        if not price_modifier_ids:
            return []
        rows = execute_cached_query(
            plpy,
            """
            SELECT "first_price_modifier_id", "second_price_modifier_id" FROM "price_modifier_combinations"
            WHERE
            "deleted_at" IS NULL AND
            (
                "first_price_modifier_id" IN ("""
            + ",".join(str(x) for x in price_modifier_ids)
            + """) OR
                "second_price_modifier_id" IN ("""
            + ",".join(str(x) for x in price_modifier_ids)
            + """)
            )
        """,
        )
        return [[row["first_price_modifier_id"], row["second_price_modifier_id"]] for row in rows]

    def _get_description_translations(self, description_id):
        if description_id is None:
            return None

        translations = execute_cached_query(
            self.plpy,
            """
            SELECT 'en' AS language_code, description
                FROM descriptions
                WHERE descriptions.id = """
            + str(description_id)
            + """
                    AND deleted_at IS NULL
            UNION
            SELECT languages.iso_code AS language_code, description_translations.description
                FROM description_translations
                INNER JOIN languages ON description_translations.language_id = languages.id
                WHERE description_translations.description_id = """
            + str(description_id)
            + """
                    AND description_translations.deleted_at IS NULL""",
        )

        result = {}
        for translation in translations:
            result[translation["language_code"]] = translation["description"]

        return result

    def _get_applicable_meal_plans(self):
        """
        Iterate meal plans and check if applicable (conditions)
        """
        if "restricted_to_meal_plan_ids" in self.meta and self.meta["restricted_to_meal_plan_ids"]:
            return loads("[" + self.meta["restricted_to_meal_plan_ids"] + "]")
        return None

    def _load_classification(self):
        cls_data = self.deserialize_properties(self.properties["type_classifications"])
        self.classification = [
            self.abstract_search.taxonomies[str(row["value_taxonomy_id"])] for row in cls_data
        ]

    def _load_meta(self):
        self.meta = {}
        meta_data = self.deserialize_properties(self.properties["type_metas"])
        for row in meta_data:
            meta_name = self.abstract_search.taxonomies[str(row["taxonomy_id"])]
            self.meta[meta_name] = row["value"]

    def _load_offer(self):
        offer_name = self._get_taxonomy_name(self.properties["offer_taxonomy_id"]) + "_offer"
        offer_class_name = underscore_to_camelcase(offer_name)
        offer_module = import_module("ots.offer." + offer_name)
        offer_class = getattr(offer_module, offer_class_name)

        self.offer = offer_class(
            plpy=self.plpy,
            from_time=self.valid_from,
            to_time=self.valid_to,
            combination_from_time=self.combination_from_time,
            combination_to_time=self.combination_to_time,
            date_ranges=self.date_ranges,
            price_modifiable_type=self.price_modifiable_type,
            price_modifiable_id=self.price_modifiable_id,
            price_modifier=self,
            abstract_search=self.abstract_search,
            age_resolver=self.age_resolver,
        )

    def copy(self, attributes=None):
        if attributes is None:
            attributes = {}
        for key, value in self.keyword_parameters.iteritems():
            if key not in attributes:
                attributes[key] = value

        return PriceModifier(**attributes)

    @staticmethod
    def deserialize_properties(properties):
        if not properties:
            return []
        return loads(properties) if type(properties) is str else properties
