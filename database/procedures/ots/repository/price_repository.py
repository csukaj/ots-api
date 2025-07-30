from ots.common.config import Config
from ots.repository.repository import Repository
from stylers.utils import execute_cached_query
from ots.repository.utils.object_mapper import ObjectMapper
from ots.repository.model.price_element_model import PriceElementModel
from ots.repository.model.price_row_meta_model import PriceRowMetaModel


class PriceRepository(Repository):
    def get_prices(self, with_age_ranges, product_ids, productable_type, productable_id):

        # with or without age ranges
        age_range_selection = ""
        age_range_joints = ""
        if with_age_ranges:
            age_range_selection = ', "age_range_taxonomies"."name" AS "age_range"'
            age_range_joints = """
                INNER JOIN "age_ranges" ON "age_ranges"."id" = "prices"."age_range_id"
                INNER JOIN "taxonomies" AS "age_range_taxonomies" ON "age_ranges"."name_taxonomy_id" = "age_range_taxonomies"."id" 
            """

        # with or without product_ids
        if product_ids is None:
            where_product_ids = """
                AND "products"."type_taxonomy_id" = {type_taxonomy_id}
            """.format(
                type_taxonomy_id=Config.PRODUCT_TYPE_ACCOMMODATION_TX_ID
            )
        else:
            where_product_ids = """
                AND
                 (
                    "products"."type_taxonomy_id" = {type_taxonomy_id}
                    AND "products"."id" IN({product_ids})
                    OR "products"."type_taxonomy_id" = {type_taxonomy_id_without_products}
                 )
            """.format(
                type_taxonomy_id=Config.PRODUCT_TYPE_PRICE_MODIFIED_ACCOMMODATION_TX_ID,
                product_ids=",".join(str(x) for x in product_ids),
                type_taxonomy_id_without_products=Config.PRODUCT_TYPE_ACCOMMODATION_TX_ID,
            )

        # the query
        sql = """
            SELECT
                "prices"."id", "prices"."product_id",
                "products"."productable_id", "products"."type_taxonomy_id" AS "product_type_taxonomy_id",
                "price_taxonomies"."name" AS "price_name", "prices"."amount", "prices"."extra", "prices"."mandatory", 
                (SELECT array_agg(DISTINCT "price_elements"."date_range_id") FROM "price_elements" WHERE "price_id"="prices"."id" AND "deleted_at" IS NULL) as "non_empty_date_ranges"
                {age_range_selection}
            FROM "prices"
                INNER JOIN "products" ON "prices"."product_id" = "products"."id"
                INNER JOIN "taxonomies" AS "price_taxonomies" ON "prices"."name_taxonomy_id" = "price_taxonomies"."id"
                {age_range_joints}
            WHERE
                "products"."productable_type" = '{productable_type}' 
                AND "products"."productable_id" = {productable_id}
                AND "products"."deleted_at" IS NULL
                AND "prices"."deleted_at" IS NULL
                {where_product_ids}
            ORDER BY "products"."type_taxonomy_id" DESC, "prices"."amount" ASC
        """.format(
            age_range_selection=age_range_selection,
            age_range_joints=age_range_joints,
            where_product_ids=where_product_ids,
            productable_type=productable_type,
            productable_id=str(productable_id),
        )

        results = execute_cached_query(self.plpy, sql)
        mapped = ObjectMapper().map_all(results, PriceRowMetaModel)
        return mapped

    def get_price_elements(self, date_ranges, prices):
        price_ids = []
        for product_type_tx_id in prices.keys():
            for price in prices[product_type_tx_id]:
                price_ids.append(str(price["id"]))

        if not price_ids:
            return []

        date_range_ids = []
        for date_ranges_list in filter(None, date_ranges.values()):
            for date_range in date_ranges_list:
                if "id" in date_range:
                    if type(date_range["id"]) is int:
                        date_range_ids.append(str(date_range["id"]))
                    else:
                        for real_id in date_range["id"]:
                            date_range_ids.append(str(real_id))
        sql = """
            SELECT
                "price_elements".*,
                "meal_plans"."id"::TEXT AS "meal_plan_id"
            FROM "price_elements"
            INNER JOIN "model_meal_plans" ON "price_elements"."model_meal_plan_id" = "model_meal_plans"."id"
            INNER JOIN "meal_plans" ON "model_meal_plans"."meal_plan_id" = "meal_plans"."id"
            WHERE
                "price_elements"."price_id" IN({price_ids}) AND
                "price_elements"."date_range_id" IN({date_range_ids}) AND
                "price_elements"."deleted_at" IS NULL AND
                "model_meal_plans"."deleted_at" IS NULL AND
                "meal_plans"."deleted_at" IS NULL
            ORDER BY "meal_plans"."id"
        """.format(
            price_ids=",".join(price_ids), date_range_ids=",".join(date_range_ids)
        )
        results = execute_cached_query(self.plpy, sql)
        elements_data = ObjectMapper().map_all(results, PriceElementModel)

        # group by date_range_id
        elements = {}
        for element in elements_data:
            elements.setdefault(element["date_range_id"], []).append(element)

        return elements
