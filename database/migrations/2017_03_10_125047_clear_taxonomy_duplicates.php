<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ClearTaxonomyDuplicates extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // create view_taxonomy_duplicates (also available in ViewSeeder, but migrations run first)
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
        
        // reset all foreign keys (in selected tables only) to the first taxonomy with identical name
        foreach (['organization_age_ranges', 'devices', 'prices'] as $table) {
            DB::statement("
                UPDATE {$table}
                SET name_taxonomy_id = (
                    SELECT view_taxonomy_duplicates.id
                    FROM view_taxonomy_duplicates
                    WHERE {$table}.name_taxonomy_id = ANY(view_taxonomy_duplicates.duplicates)
                )
                WHERE
                1 = (
                    SELECT COUNT(view_taxonomy_duplicates.id)
                    FROM view_taxonomy_duplicates
                    WHERE {$table}.name_taxonomy_id = ANY(view_taxonomy_duplicates.duplicates)
                )
            ");
        }
        
        // delete unused taxonomies
        DB::statement("
            DELETE FROM taxonomies
            WHERE 1 = (
                SELECT COUNT(id)
                FROM view_taxonomy_duplicates
                WHERE taxonomies.id = ANY(view_taxonomy_duplicates.duplicates)
            )
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
