<?php

namespace Tests\Integration\Models;

use App\DateRange;
use App\Organization;
use App\Facades\Config;
use Tests\TestCase;

class DateRangeTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models()
    {
        $organization = factory(Organization::class, 'accommodation')->create();
        $dateRange = factory(DateRange::class)->create([
            'date_rangeable_id' => $organization->id,
            'from_time' => '2026-05-31',
            'to_time' => '2026-06-30',
            'type_taxonomy_id' => Config::get('taxonomies.date_range_types.open')
        ]);
        return [$organization, $dateRange];
    }

    /**
     * @test
     */
    function it_can_count_interval_overlap_days()
    {
        list(, $dateRange) = $this->prepare_models();

        $this->assertEquals(0, $dateRange->getOverlapDays('2026-05-30', '2026-05-31'));
        $this->assertEquals(1, $dateRange->getOverlapDays('2026-05-30', '2026-06-01'));
        $this->assertEquals(3, $dateRange->getOverlapDays('2026-05-30', '2026-06-03'));
        $this->assertEquals(2, $dateRange->getOverlapDays('2026-06-01', '2026-06-03'));
        $this->assertEquals(1, $dateRange->getOverlapDays('2026-06-29', '2026-06-30'));
        $this->assertEquals(2, $dateRange->getOverlapDays('2026-06-29', '2026-07-02'));
        $this->assertEquals(0, $dateRange->getOverlapDays('2026-07-01', '2026-07-15'));
        $this->assertEquals(31, $dateRange->getOverlapDays('2026-05-30', '2026-07-15'));
    }

    /**
     * @test
     */
    function it_can_list_date_ranges_in_interval()
    {
        list($organization, $dateRange) = $this->prepare_models();

        $dateRanges = DateRange::getDateRangesInInterval(Organization::class, $organization->id, '2026-05-15', '2026-07-15', Config::get('taxonomies.date_range_types.open'));
        $this->assertEquals(1, count($dateRanges));
        $this->assertEquals($dateRange->id, $dateRanges[0]->id);

        $dateRanges = DateRange::getDateRangesInInterval(Organization::class, $organization->id, '2026-04-15', '2026-05-15', Config::get('taxonomies.date_range_types.open'));
        $this->assertEquals(0, count($dateRanges));
    }

    /**
     * @test
     */
    function it_can_detect_date_ranges_in_interval()
    {
        list($organization,) = $this->prepare_models();

        $this->assertTrue(DateRange::hasDateRanges(Organization::class, $organization->id, '2026-05-15', '2026-07-15'));
        $this->assertFalse(DateRange::hasDateRanges(Organization::class, $organization->id, '2026-04-15', '2026-05-15'));
    }
}
