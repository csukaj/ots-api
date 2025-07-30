<?php

namespace Tests\Integration\Database;

use App\Facades\Config;
use App\Organization;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrganizationHasDefaultAgeRangeTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    public function has_adult_age_range()
    {
        $orgTypesWithoutAgeRange = [
            Config::getOrFail('taxonomies.organization_types.hotel_chain.id'),
            Config::getOrFail('taxonomies.organization_types.ship_company.id'),
            Config::getOrFail('taxonomies.organization_types.ship.id'),
            Config::getOrFail('taxonomies.organization_types.supplier.id')
        ];

        $inconsistencies = DB::select(
            'SELECT organizations.* 
            FROM organizations 
            WHERE id NOT IN (
                SELECT age_rangeable_id 
                FROM age_ranges
                WHERE name_taxonomy_id = ' . Config::getOrFail('taxonomies.age_ranges.adult.id') . '
                AND age_rangeable_type = \'' . Organization::class . '\'
                AND age_ranges.deleted_at IS NULL
            )
            AND organizations.type_taxonomy_id NOT IN (' . implode(',', $orgTypesWithoutAgeRange) . ')
            AND organizations.deleted_at IS NULL'
        );
        $this->assertEmpty($inconsistencies);

        $inconsistencies2 = DB::select(
            'SELECT organizations.* 
            FROM organizations 
            WHERE id NOT IN (
                SELECT age_rangeable_id FROM age_ranges
                WHERE to_age IS NULL
                AND age_rangeable_type = \'' . Organization::class . '\'
                AND age_ranges.deleted_at IS NULL
            )
            AND organizations.deleted_at IS NULL
            AND organizations.type_taxonomy_id NOT IN (' . implode(',', $orgTypesWithoutAgeRange) . ')'
        );
        $this->assertEmpty($inconsistencies2);
    }
}
