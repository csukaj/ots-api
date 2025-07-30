<?php

use Illuminate\Database\Seeder;
use App\Facades\Config;
use Illuminate\Support\Facades\DB;

class ViewSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $accommodationType = Config::get('taxonomies.organization_types.accommodation.id');
        $roomType = Config::get('taxonomies.devices.room');
        DB::statement("
            CREATE OR REPLACE VIEW view_accommodation_rooms AS
            SELECT
                organizations.id AS organization_id,
                devices.id AS device_id,
                device_usages.id AS device_usage_id,
                taxonomies.name,
                device_usage_elements.amount,
                to_json(devices) as device
            FROM organizations
            INNER JOIN devices ON organizations.id = devices.deviceable_id AND devices.deviceable_type = 'App\\Organization' AND devices.deleted_at IS NULL
            INNER JOIN device_usages ON devices.id = device_usages.device_id AND device_usages.deleted_at IS NULL
            INNER JOIN device_usage_elements ON device_usages.id = device_usage_elements.device_usage_id AND device_usage_elements.deleted_at IS NULL
            INNER JOIN age_ranges ON device_usage_elements.age_range_id = age_ranges.id AND age_ranges.deleted_at IS NULL
            INNER JOIN taxonomies ON taxonomies.id = age_ranges.name_taxonomy_id AND taxonomies.deleted_at IS NULL
            WHERE organizations.type_taxonomy_id = {$accommodationType} AND devices.type_taxonomy_id = {$roomType} AND organizations.deleted_at IS NULL
            ORDER BY organization_id, device_id, device_usage_id
        ");

        $shipGroupType = Config::get('taxonomies.organization_group_types.ship_group.id');
        $cabinType = Config::get('taxonomies.devices.cabin');
        DB::statement("
            CREATE OR REPLACE VIEW view_ship_cabins AS
            SELECT
                organization_groups.id AS organization_group_id,
                devices.id AS device_id,
                device_usages.id AS device_usage_id,
                taxonomies.name,
                device_usage_elements.amount,
                to_json(devices) as device
            FROM organization_groups
            INNER JOIN devices ON organization_groups.id = devices.deviceable_id AND devices.deviceable_type = 'App\\ShipGroup' AND devices.deleted_at IS NULL
            INNER JOIN device_usages ON devices.id = device_usages.device_id AND device_usages.deleted_at IS NULL
            INNER JOIN device_usage_elements ON device_usages.id = device_usage_elements.device_usage_id AND device_usage_elements.deleted_at IS NULL
            INNER JOIN age_ranges ON device_usage_elements.age_range_id = age_ranges.id AND age_ranges.deleted_at IS NULL
            INNER JOIN taxonomies ON taxonomies.id = age_ranges.name_taxonomy_id AND taxonomies.deleted_at IS NULL
            WHERE organization_groups.type_taxonomy_id = {$shipGroupType} AND devices.type_taxonomy_id = {$cabinType} AND organization_groups.deleted_at IS NULL
            ORDER BY organization_group_id, device_id, device_usage_id
        ");

        $shipGroupType = Config::get('taxonomies.organization_group_types.ship_group.id');
        $cabinType = Config::get('taxonomies.devices.cabin');
        DB::statement("
            CREATE OR REPLACE VIEW view_ships AS
            SELECT
                organization_groups.id AS organization_group_id,
                taxonomies.name,
                SUM(device_usage_elements.amount * devices.amount) AS amount,
                to_json(devices) as device
            FROM organization_groups
            INNER JOIN devices ON organization_groups.id = devices.deviceable_id AND devices.deviceable_type = 'App\\ShipGroup' AND devices.deleted_at IS NULL
            INNER JOIN device_usages ON devices.id = device_usages.device_id AND device_usages.deleted_at IS NULL
            INNER JOIN device_usage_elements ON device_usages.id = device_usage_elements.device_usage_id AND device_usage_elements.deleted_at IS NULL
            INNER JOIN age_ranges ON device_usage_elements.age_range_id = age_ranges.id AND age_ranges.deleted_at IS NULL
            INNER JOIN taxonomies ON taxonomies.id = age_ranges.name_taxonomy_id AND taxonomies.deleted_at IS NULL
            WHERE organization_groups.type_taxonomy_id = {$shipGroupType} AND devices.type_taxonomy_id = {$cabinType} AND organization_groups.deleted_at IS NULL
            GROUP BY organization_groups.id, taxonomies.name, devices.*
            ORDER BY organization_group_id
        ");

        DB::statement("
            CREATE OR REPLACE VIEW view_room_usages AS
            SELECT
                device_usages.device_id AS device_id,
                device_usages.id AS device_usage_id,
                ARRAY_AGG(taxonomies.name ORDER BY taxonomies.name) AS age_range_names,
                ARRAY_AGG(device_usage_elements.amount ORDER BY taxonomies.name) AS age_range_amounts
            FROM device_usages
            LEFT JOIN device_usage_elements ON device_usages.id = device_usage_elements.device_usage_id AND device_usage_elements.deleted_at IS NULL
            INNER JOIN age_ranges ON device_usage_elements.age_range_id = age_ranges.id AND age_ranges.deleted_at IS NULL
            INNER JOIN taxonomies ON age_ranges.name_taxonomy_id = taxonomies.id AND taxonomies.deleted_at IS NULL
            WHERE device_usages.deleted_at IS NULL
            GROUP BY device_usages.id
            ORDER BY device_id, device_usage_id
        ");
        
        DB::statement("
            CREATE OR REPLACE VIEW view_taxonomy_duplicates AS
            WITH temp AS (
                SELECT
                    ROW_NUMBER()
                    OVER (PARTITION BY LOWER(t1.name) ORDER BY t1.id) AS row_number,
                    t1.id,
                    t1.name,
                    t1.parent_id,
                    ARRAY_AGG(t2.id) AS duplicates
                FROM taxonomies AS t1
                    INNER JOIN taxonomies AS t2 ON t1.id != t2.id
                WHERE
                    t1.parent_id = t2.parent_id AND
                    (
                        LOWER(t1.name) = LOWER(t2.name) OR LOWER(CONCAT(t1.name, '.')) = LOWER(t2.name)
                    )
                GROUP BY t1.id, t1.name
                ORDER BY t1.parent_id, t1.id
            )
            SELECT temp.parent_id, temp.name, temp.id, temp.duplicates
            FROM temp
            WHERE row_number = 1
        ");
    }

}
