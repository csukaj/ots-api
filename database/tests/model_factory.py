from datetime import datetime
from json import dumps
from time import strftime

from ots.common.age_resolver import AgeResolver
from ots.price_modifier.price_modifier import PriceModifier
from ots.search.room_search import RoomSearch


class ModelFactory:

    def __init__(self, plpy_mocker):
        self.plpy_mocker = plpy_mocker

    def price_modifier(self, **params):
        offer_taxonomy = self.plpy_mocker.execute('SELECT * FROM taxonomies WHERE parent_id = 82 ORDER BY id LIMIT 1')[
            0]
        first_price_modifier = self.plpy_mocker.execute('SELECT * FROM price_modifiers ORDER BY id LIMIT 1')[0]

        params['plpy'] = params.get('plpy', self.plpy_mocker)
        params['request'] = params.get('request', None)
        params['properties'] = params.get('properties', self.price_modifier_properties(first_price_modifier['id']))
        params['from_time'] = datetime.strptime(params.get('from_time', '2027-01-01 12:00:00'), '%Y-%m-%d %H:%M:%S')
        params['to_time'] = datetime.strptime(params.get('to_time', '2027-01-10 12:00:00'), '%Y-%m-%d %H:%M:%S')
        params['request_from_time'] = params.get('request_from_time',
                                                 datetime.strptime('2027-01-01 12:00:00', '%Y-%m-%d %H:%M:%S'))
        params['request_to_time'] = params.get('request_to_time',
                                               datetime.strptime('2027-01-10 12:00:00', '%Y-%m-%d %H:%M:%S'))
        params['date_ranges'] = params.get('date_ranges', None)
        params['price_modifiable_type'] = params.get('price_modifiable_type', 'App\\\\Organization')
        params['price_modifiable_id'] = params.get('price_modifiable_id', 1)
        params['abstract_search'] = params.get('abstract_search', None)
        params['available_devices'] = params.get('available_devices', [])
        return PriceModifier(**params)

    def room_usage(self, usage_elements=None):
        return usage_elements if usage_elements is not None else [
            {'age': 21, 'amount': 1}
        ]

    def room_request(self, **params):
        return {
            'usage': self.room_usage(params['usage_elements'] if 'usage_elements' in params else None)
        }

    def request(self, **params):
        request = params.get('rooms', [self.room_request()])
        return dumps(request) if params.get('json', False) else request

    def room_search(self, **params):
        params['plpy'] = params.get('plpy', self.plpy_mocker)
        params['organization_id'] = params.get('organization_id', 1)
        params['params'] = dumps({
            'request': params.get('request', self.request(**params)),
            'interval': {
                'date_from': params.get('from_date', '2027-01-01'),
                'date_to': params.get('to_date', '2027-01-10')
            },
            'booking_date': params.get('booking_date', strftime('%Y-%m-%d')),
            'wedding_date': params.get('wedding_date', None)
        })
        room_search = RoomSearch(**params)
        if 'room_search' in params:
            room_search.set_applicable_rooms(params['room_search'])
        devices = self.plpy_mocker.execute(
            """SELECT * FROM "devices" WHERE "deviceable_id" = """ + str(params['organization_id']) + """ AND deleted_at IS NULL"""
        )
        for device in devices:
            room_search.room_matcher.devices_data[device['id']] = device
        return room_search

    def date_ranges(self, date_rangeable_id):
        date_rangeable_type = 'App\\Organization'
        sql = '''
                SELECT * FROM date_ranges 
                WHERE type_taxonomy_id = {type_tx_id} 
                    AND date_rangeable_type = '{date_rangeable_type}'
                    AND date_rangeable_id = {date_rangeable_id}'''
        return {
            62: self.plpy_mocker.execute(
                sql.format(
                    date_rangeable_type=date_rangeable_type,
                    date_rangeable_id=str(date_rangeable_id),
                    type_tx_id=62
                )
            ),
            63: self.plpy_mocker.execute(
                sql.format(
                    date_rangeable_type=date_rangeable_type,
                    date_rangeable_id=str(date_rangeable_id),
                    type_tx_id=63
                )
            ),
            164: self.plpy_mocker.execute(
                sql.format(
                    date_rangeable_type=date_rangeable_type,
                    date_rangeable_id=str(date_rangeable_id),
                    type_tx_id=164
                )
            )
        }

    def open_date_ranges(self, date_rangeable_id):
        return self.date_ranges(date_rangeable_id)[62]

    def open_date_range(self, date_rangeable_id, index=0):
        return self.open_date_ranges(date_rangeable_id)[index]

    def device(self, deviceable_type, deviceable_id, index=0):
        return self.plpy_mocker.execute('''
            SELECT * FROM devices WHERE
            deviceable_id = ''' + str(deviceable_id) + ''' AND
            deviceable_type = \'''' + str(deviceable_type) + '''\'
            ORDER BY id ASC'''
                                        )[index]

    def available_devices(self, deviceable_type, deviceable_id):
        devices = self.plpy_mocker.execute('''
                    SELECT * FROM devices WHERE
                    deviceable_id = ''' + str(deviceable_id) + ''' AND
                    deviceable_type = \'''' + str(deviceable_type) + '''\'
                    ORDER BY id ASC'''
                                           )
        available_devices = []
        for device in devices:
            available_devices.append({
                'device_id': device['id'],
                'available': device['amount'],
                'is_overbooked': False,
                'usage_pairs': [0]
            })

        return available_devices

    def price(self, device_id, index=0):
        return self.plpy_mocker.execute('''
            SELECT prices.* FROM prices
            INNER JOIN products ON prices.product_id = products.id
            WHERE productable_type = 'App\\Device' AND productable_id = ''' + str(device_id)
                                        )[index]

    def age_resolver(self, age_rangeable_type, age_rangeable_id):
        return AgeResolver(self.plpy_mocker, age_rangeable_type, age_rangeable_id)

    def organization(self, organization_id, index=0):
        return self.plpy_mocker.execute('''
            SELECT * FROM organizations WHERE
            organization_id = ''' + str(organization_id) + '''
            ORDER BY id ASC'''
                                        )[index]

    def datetime(self, datetime_string='2026-06-01', date_format='%Y-%m-%d'):
        return datetime.strptime(datetime_string, date_format)

    def price_modifier_properties(self, price_modifier_id=0, index=0):
        price_modifier_id_str = '' if not price_modifier_id else """AND "price_modifiers"."id" =""" + str(
            price_modifier_id)
        price_modifier_sql = """
                SELECT 
                                "price_modifiers"."id",
                                "price_modifiers"."name_description_id",
                                "price_modifiers"."modifier_type_taxonomy_id",
                                "price_modifiers"."condition_taxonomy_id",
                                "price_modifiers"."offer_taxonomy_id",
                                "price_modifiers"."priority",
                                "price_modifiers"."description_description_id",
                                "condition_tx"."parent_id" AS "application_level_taxonomy_id",
                                -- --
                                (
                           SELECT array_to_json(array_agg(row_to_json(d)))
                           FROM (
                                  SELECT
                                    price_modifier_metas.taxonomy_id,
                                    price_modifier_metas.value
                                  FROM price_modifier_metas
                                  WHERE price_modifier_metas.price_modifier_id = price_modifiers.id AND
                                        price_modifier_metas.deleted_at IS NULL
                                ) d
                         ) AS type_metas,
                         (
                           SELECT array_to_json(array_agg(row_to_json(d)))
                           FROM (
                                  SELECT value_taxonomy_id
                                  FROM price_modifier_classifications
                                  WHERE price_modifier_classifications.price_modifier_id = price_modifiers.id AND
                                        price_modifier_classifications.deleted_at IS NULL
                                ) d
                         ) AS type_classifications,
                         (
                           SELECT array_to_json(array_agg(row_to_json(d)))
                           FROM (
                                  SELECT
                                    taxonomy_id,
                                    value
                                  FROM offer_metas
                                  WHERE price_modifier_id = price_modifiers.id AND deleted_at IS NULL
                                ) d
                         ) AS offer_metas,
                         (
                           SELECT array_to_json(array_agg(row_to_json(d)))
                           FROM (
                                  SELECT value_taxonomy_id
                                  FROM offer_classifications
                                  WHERE price_modifier_id = price_modifiers.id AND deleted_at IS NULL
                                ) d
                         ) AS offer_classifications
                                -- --
                                FROM "price_modifiers"
                                INNER JOIN "taxonomies" AS "condition_tx" ON "condition_tx"."id" = "price_modifiers"."condition_taxonomy_id"
                                WHERE "price_modifiers"."deleted_at" IS NULL
                                AND "price_modifiers"."is_active"
                """ + price_modifier_id_str + """
                ORDER BY "price_modifiers"."priority" ASC
            """
        return self.plpy_mocker.execute(price_modifier_sql)[index]

    def room_offer(self, **params):
        return {
            'meal_plan_id': 2,
            'meal_plan': 'b/b',
            'original_price': 100,
            'discounted_price': 100,
            'order_itemable_index': 0,
            'room_request': params.get('room_request', self.room_request()),
            'discounts': []
        }

    def meal_plans(self, meal_planable_type, meal_planable_id):
        sql = '''
            SELECT
                "meal_plans"."id" AS "meal_plan_id",
                "taxonomies"."name" AS "meal_plan_name"
            FROM "model_meal_plans"
            INNER JOIN "meal_plans" ON "model_meal_plans"."meal_plan_id" = "meal_plans"."id"
            INNER JOIN "taxonomies" ON "meal_plans"."name_taxonomy_id" = "taxonomies"."id"
            WHERE
                "model_meal_plans"."meal_planable_type" = \'''' + str(meal_planable_type) + '''\' AND
                "model_meal_plans"."meal_planable_id" = ''' + str(meal_planable_id) + ''' AND
                "model_meal_plans"."deleted_at" IS NULL AND
                "meal_plans"."deleted_at" IS NULL AND
                "taxonomies"."deleted_at" IS NULL
        '''
        result = self.plpy_mocker.execute(sql)
        meal_plans = {}
        for row in result:
            meal_plans[str(row['meal_plan_id'])] = row['meal_plan_name']
        return meal_plans
