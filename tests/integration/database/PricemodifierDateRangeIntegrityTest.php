<?php

namespace Tests\Integration\Database;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PricemodifierDateRangeIntegrityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    
    /**
     * @test
     */
    public function there_are_no_overlapping_date_ranges_set_for_any_priceModifier() {
        $result = [];
        $priceModifiers = DB::select("
            SELECT DISTINCT date_ranges.date_rangeable_id, price_modifiers.id, descriptions.description
            FROM price_modifiers
            INNER JOIN price_modifier_periods ON price_modifiers.id = price_modifier_periods.price_modifier_id
            INNER JOIN date_ranges ON price_modifier_periods.date_range_id = date_ranges.id
            INNER JOIN descriptions ON descriptions.id = price_modifiers.name_description_id
            WHERE
                price_modifiers.deleted_at IS NULL AND
                price_modifier_periods.deleted_at IS NULL
            GROUP BY date_ranges.date_rangeable_id, price_modifiers.id, descriptions.description
            HAVING COUNT(price_modifier_periods.id) > 1
            ORDER BY price_modifiers.id
        ");
        
        foreach ($priceModifiers as $priceModifier) {
            $dateRanges = DB::select("
                SELECT DISTINCT date_ranges.*
                FROM price_modifier_periods
                INNER JOIN date_ranges ON price_modifier_periods.date_range_id = date_ranges.id
                WHERE price_modifier_periods.price_modifier_id = {$priceModifier->id} AND price_modifier_periods.deleted_at IS NULL
            ");
            foreach ($dateRanges as $dateRange) {
                $overlappingDateRanges = DB::select("
                    SELECT DISTINCT date_ranges.*
                    FROM price_modifier_periods
                    INNER JOIN date_ranges ON price_modifier_periods.date_range_id = date_ranges.id
                    WHERE
                        price_modifier_periods.deleted_at IS NULL AND
                        date_ranges.deleted_at IS NULL AND
                        price_modifier_periods.price_modifier_id = {$priceModifier->id} AND
                        date_ranges.id != {$dateRange->id} AND
                        (DATE '{$dateRange->from_time}', DATE '{$dateRange->to_time}') OVERLAPS
                        (date_ranges.from_time, date_ranges.to_time)
                ");
                if (count($overlappingDateRanges)) {
                    $result[$priceModifier->id] = (array)$priceModifier;
                }
            }
        }
        $this->assertCount(0, $result);
    }
}
