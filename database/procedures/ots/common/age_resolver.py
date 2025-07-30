from stylers.utils import execute_cached_query


class AgeResolver:
    def __init__(self, plpy, age_rangeable_type, age_rangeable_id):
        self.plpy = plpy
        self.age_rangeable_type = age_rangeable_type
        self.age_rangeable_id = age_rangeable_id
        self.age_ranges = self._get_age_ranges()

    def _get_age_ranges(self):
        return execute_cached_query(
            self.plpy,
            """
            SELECT
                age_ranges.id,
                age_ranges.from_age,
                age_ranges.to_age,
                taxonomies.name AS name,
                age_ranges.banned,
                age_ranges.free
            FROM age_ranges
            INNER JOIN taxonomies ON age_ranges.name_taxonomy_id = taxonomies.id
            WHERE 
                age_ranges.age_rangeable_type = '"""
            + str(self.age_rangeable_type)
            + """' 
                AND age_ranges.age_rangeable_id = """
            + str(self.age_rangeable_id)
            + """ 
                AND age_ranges.deleted_at IS NULL
            ORDER BY age_ranges.from_age ASC
        """,
        )

    @staticmethod
    def _in_range(age, age_range):
        return age <= age_range["to_age"] or age_range["to_age"] is None

    def _get_range_of_age(self, age):
        for age_range in self.age_ranges:
            if self._in_range(age, age_range):
                return age_range
        raise ValueError(
            "Could not resolve age of "
            + str(age)
            + " for "
            + self.age_rangeable_type
            + " #"
            + str(self.age_rangeable_id)
        )

    """
    Converts room usage to age groups and amounts
    E.g. [{"age": 7, "amount": 2}, {"age": 21, "amount": 1}] => {"adult": 1, "child": 2}
    """

    def resolve_room_usage(self, room_usage, remove_free_ranges=False):
        room_usage.sort(key=lambda item: item["age"], reverse=True)
        result = {}
        for usage_item in room_usage:
            age_range = self._get_range_of_age(usage_item["age"])
            if not remove_free_ranges or not age_range["free"]:
                result[age_range["name"]] = result.get(age_range["name"], 0) + usage_item["amount"]
        return result

    """
    Returns all age ranges of the organization in a dictionary
    E.g. {"adult": {"id": 1, "from_age": 0, "to_age": None, "name": "adult"}}
    """

    def get_age_ranges_dict(self):
        age_range_dict = {}
        for age_range in self.age_ranges:
            age_range_dict[age_range["name"]] = age_range
        return age_range_dict

    """
       Returns age range of the organization of given name
       E.g. {"id": 1, "from_age": 0, "to_age": None, "name": "adult"}
       """

    def get_age_range_by_name(self, name):
        found = [age_range for age_range in self.age_ranges if age_range["name"] == name]
        return found[0] if found else None
