<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command can be used for testing anything';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $result = [];
        $priceModifiers = DB::select("
            SELECT DISTINCT organization_date_ranges.organization_id, price_modifiers.id, descriptions.description
            FROM price_modifiers
            INNER JOIN price_modifier_periods ON price_modifiers.id = price_modifier_periods.price_modifier_id
            INNER JOIN organization_date_ranges ON price_modifier_periods.organization_date_range_id = organization_date_ranges.id
            INNER JOIN descriptions ON descriptions.id = price_modifiers.name_description_id
            WHERE
                price_modifiers.deleted_at IS NULL AND
                price_modifier_periods.deleted_at IS NULL
            GROUP BY organization_date_ranges.organization_id, price_modifiers.id, descriptions.description
            HAVING COUNT(price_modifier_periods.id) > 1
            ORDER BY price_modifiers.id
        ");
        
        foreach ($priceModifiers as $priceModifier) {
            $dateRanges = DB::select("
                SELECT DISTINCT organization_date_ranges.*
                FROM price_modifier_periods
                INNER JOIN organization_date_ranges ON price_modifier_periods.organization_date_range_id = organization_date_ranges.id
                WHERE price_modifier_periods.price_modifier_id = {$priceModifier->id} AND price_modifier_periods.deleted_at IS NULL
            ");
            foreach ($dateRanges as $dateRange) {
                $overlappingDateRanges = DB::select("
                    SELECT DISTINCT organization_date_ranges.*
                    FROM price_modifier_periods
                    INNER JOIN organization_date_ranges ON price_modifier_periods.organization_date_range_id = organization_date_ranges.id
                    WHERE
                        price_modifier_periods.deleted_at IS NULL AND
                        organization_date_ranges.deleted_at IS NULL AND
                        price_modifier_periods.price_modifier_id = {$priceModifier->id} AND
                        organization_date_ranges.id != {$dateRange->id} AND
                        (DATE '{$dateRange->from_time}', DATE '{$dateRange->to_time}') OVERLAPS
                        (organization_date_ranges.from_time, organization_date_ranges.to_time)
                ");
                if (count($overlappingDateRanges)) {
                    $result[$priceModifier->id] = (array)$priceModifier;
                }
            }
        }
        
        $this->table(['Organization ID', 'Price Modifier ID', 'Price Modifier Name'], $result);
        echo "COUNT: " . count($result) . "\n";
    }
}
