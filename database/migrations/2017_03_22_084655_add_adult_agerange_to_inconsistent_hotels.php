<?php

use App\Facades\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAdultAgerangeToInconsistentHotels extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $affected = DB::select('
SELECT
  organizations.id,
  count(organization_age_ranges.organization_id) AS age_range_count
FROM organizations
  LEFT JOIN organization_age_ranges ON organizations.id = organization_age_ranges.organization_id
WHERE organizations.id IN (SELECT id
                           FROM organizations
                           WHERE id NOT IN (SELECT organization_id
                                            FROM organization_age_ranges
                                            WHERE name_taxonomy_id = ' . Config::getOrFail('taxonomies.age_ranges.adult.id') . '
                                           )
                          )
GROUP BY organizations.id'
        );

        foreach ($affected as $record) {
            if ($record->age_range_count > 0) {
                //"needs to update to adult age range @ hotel#{$record->id}\n";
                DB::statement('
                        UPDATE organization_age_ranges 
                        SET name_taxonomy_id=' . Config::getOrFail('taxonomies.age_ranges.adult.id') . '
                        WHERE organization_id = ' . $record->id . ' 
                            AND to_age IS NULL
                            AND deleted_at IS NULL');
            } else {
                //"needs to add adult age range @ hotel#{$record->id}\n";
                DB::statement('
                    INSERT INTO organization_age_ranges 
                        (from_age, to_age, organization_id, name_taxonomy_id, created_at, updated_at) 
                        VALUES(0,        NULL,   ' . $record->id . ', ' . Config::getOrFail('taxonomies.age_ranges.adult.id') . ', NOW(), NOW())
                         ');
            }
        }
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
