<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class ClearAgeRangeTaxonomies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $priceNameTx = Taxonomy::findOrFail(140);

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10032
                WHERE name_taxonomy_id = 17
            ');

            try {
                Taxonomy::findOrFail(10022)->makeChildOf($priceNameTx);
            } catch (Exception $e) {
                //
            }

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10349
                WHERE name_taxonomy_id = 10027
            ');

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10350
                WHERE name_taxonomy_id = 10028
            ');

            try {
                Taxonomy::findOrFail(10031)->makeChildOf($priceNameTx);
            } catch (Exception $e) {
                //
            }

            try {
                Taxonomy::findOrFail(10032)->makeChildOf($priceNameTx);
            } catch (Exception $e) {
                //
            }

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10348
                WHERE name_taxonomy_id = 10043
            ');

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10384
                WHERE name_taxonomy_id = 10045
            ');

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10385
                WHERE name_taxonomy_id = 10060
            ');

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10399
                WHERE name_taxonomy_id = 10061
            ');

            try {
                Taxonomy::findOrFail(10062)->makeChildOf($priceNameTx);
            } catch (Exception $e) {
                //
            }

            try {
                Taxonomy::findOrFail(10081)->makeChildOf($priceNameTx);
            } catch (Exception $e) {
                //
            }

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10369
                WHERE name_taxonomy_id = 10082
            ');

            DB::statement('
                UPDATE prices
                SET name_taxonomy_id = 10399
                WHERE name_taxonomy_id = 10261
            ');

            ///

            DB::statement('
                UPDATE organization_age_ranges
                SET name_taxonomy_id = 17
                WHERE name_taxonomy_id IN(10028, 10032, 10061)
            ');

            DB::statement('
                UPDATE organization_age_ranges
                SET name_taxonomy_id = 18
                WHERE name_taxonomy_id = 10410
            ');

            ///

            DB::statement('
                DELETE FROM taxonomies
                WHERE
                    parent_id = 15 AND
                    id NOT IN (SELECT name_taxonomy_id FROM organization_age_ranges) AND
                    id NOT IN (SELECT name_taxonomy_id FROM prices)
            ');
        } catch (Exception $e) {
            //
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
