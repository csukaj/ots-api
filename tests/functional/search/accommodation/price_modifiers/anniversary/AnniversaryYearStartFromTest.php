<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers\Anniversary;

use Tests\TestCase;

class AnniversaryYearStartFromTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $wedding = null) {
        if (!$wedding) {
            $wedding = '2022-11-20';
        }
        return $this->prepareAccommodationSearchResult($interval, 'Hotel I', [['usage' => [['age' => 21, 'amount' => 2]]]], $wedding);        
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_in_the_interval_with_in_range_days() {
        $actual = $this->prepare(['date_from' => '2027-01-02', 'date_to' => '2027-01-04'],'2022-01-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(320, $actual['original_price']);
        $this->assertEquals(120, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 200 / 320 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_in_the_interval_with_in_range_days_3_years_anniversary() {
        $actual = $this->prepare(['date_from' => '2027-01-02', 'date_to' => '2027-01-04'],'2024-01-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(320, $actual['original_price']);
        $this->assertEquals(120, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 200 / 320 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_no_price_modifier_2_years_not_exceeded_1() {
        $actual = $this->prepare(['date_from' => '2027-01-01', 'date_to' => '2027-01-03'],'2026-12-28')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(320, $actual['original_price']);
        $this->assertEquals(320, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_no_price_modifier_2_years_not_exceeded_2() {
        $actual = $this->prepare(['date_from' => '2027-01-01', 'date_to' => '2027-01-03'],'2025-12-28')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Double Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(320, $actual['original_price']);
        $this->assertEquals(320, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
