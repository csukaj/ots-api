from ots.repository.repository import Repository
from stylers.utils import execute_cached_query


class ModelMealPlanRepository(Repository):
    def _get_meal_plans_for_date_ranges(self, date_range_ids):
        # type: (List[str]) -> List[Dict[str, str]]

        if len(date_range_ids) == 0:
            return []

        query = """
            SELECT date_range_id, array_agg(meal_plan_id ORDER BY meal_plan_id DESC) as meal_plans
            FROM model_meal_plans
            WHERE
                deleted_at IS NULL
                AND date_range_id IN ({date_range_ids})
            GROUP BY date_range_id
            """.format(
            date_range_ids=",".join(date_range_ids)
        )

        return execute_cached_query(self.plpy, query)

    def get_common_meal_plans_with_date_ranges(self, date_range_ids):
        # type: (list) -> set

        meal_plans = self._get_meal_plans_for_date_ranges(date_range_ids)

        common_meal_plans = set(meal_plans[0]["meal_plans"]) if len(meal_plans) else set({})
        for mp_range in meal_plans[1:]:
            common_meal_plans = common_meal_plans.intersection(set(mp_range["meal_plans"]))

        return common_meal_plans if common_meal_plans else set([])
