<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers\Anniversary;

use Tests\TestCase;

class AnniversaryYearLeapTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $wedding = null) {
        if (!$wedding) {
            $wedding = '2022-12-31';
        }
        return $this->prepareAccommodationSearchResult($interval, 'Hotel Bride', [['usage' => [['age' => 21, 'amount' => 2]]]], $wedding);        
    }

    /**
     * @test
     */
    public function in_30_days_range_and_anniversary_is_before_from_date_and_year_leap() {
        $actual = $this->prepare(['date_from' => '2027-01-01', 'date_to' => '2027-01-03'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(200, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 100 - 200 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function in_30_days_range_and_anniversary_is_equal_to_date_from() {
        $actual = $this->prepare(['date_from' => '2027-01-01', 'date_to' => '2027-01-03'],'2022-01-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(0, $actual['discounted_price']);
        $this->assertEquals(['value' => 400, 'percentage' => 100 - 0 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function in_30_days_range_and_anniversary_is_after_to_date_from() {
        $actual = $this->prepare(['date_from' => '2027-01-01', 'date_to' => '2027-01-03'],'2022-01-04')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(200, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 100 - 200 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function in_60_days_range_and_anniversary_is_after_year_leap() {
        //TODO: cond_anniversary_in_range_days is bad
        $actual = $this->prepare(['date_from' => '2027-12-01', 'date_to' => '2027-12-03'],'2022-01-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(200, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 100 - 200 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function in_30_days_range_and_anniversary_is_after_year_leap_and_period_is_5() {
        $actual = $this->prepare(['date_from' => '2028-12-25', 'date_to' => '2028-12-27'],'2024-01-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(100, $actual['discounted_price']);
        $this->assertEquals(['value' => 300, 'percentage' => 100 - 100 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function in_30_days_range_and_anniversary_is_before_year_leap_and_period_is_5() {
        $actual = $this->prepare(['date_from' => '2028-01-01', 'date_to' => '2028-01-03'],'2022-12-28')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(100, $actual['discounted_price']);
        $this->assertEquals(['value' => 300, 'percentage' => 100 - 100 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function anniversary_is_in_30_days_range_and_before_year_leap_and_period_is_5_but_only_once() {
        $actual = $this->prepare(['date_from' => '2028-01-11', 'date_to' => '2028-01-13'],'2022-12-28')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(100, $actual['discounted_price']);
        $this->assertEquals(['value' => 300, 'percentage' => 100 - 100 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function anniversary_is_in_30_days_range_and_before_year_leap_and_period_is_5_but_only_once_10_year_anniversary() {
        $actual = $this->prepare(['date_from' => '2028-01-11', 'date_to' => '2028-01-13'],'2017-12-28')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(400, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function in_30_days_range_and_anniversary_is_before_year_leap_and_period_is_5_and_10_years_anniversary() {
        $actual = $this->prepare(['date_from' => '2028-01-01', 'date_to' => '2028-01-03'],'2017-12-28')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(100, $actual['discounted_price']);
        $this->assertEquals(['value' => 300, 'percentage' => 100 - 100 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function not_in_30_days_range_and_anniversary_is_before_year_leap_and_period_is_5() {
        $actual = $this->prepare(['date_from' => '2028-01-01', 'date_to' => '2028-01-03'],'2022-12-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(400, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function not_match_with_period_in_30_days_range_and_anniversary_is_before_year_leap_and_period_is_5() {
        $actual = $this->prepare(['date_from' => '2028-01-01', 'date_to' => '2028-01-03'],'2023-12-28')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(400, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function in_30_days_range_only_once_and_anniversary_is_before_from_date() {
        $actual = $this->prepare(['date_from' => '2027-12-06', 'date_to' => '2027-12-08'],'2026-12-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(200, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 100 - 200 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function in_30_days_range_only_once_and_anniversary_is_after_from_date() {
        $actual = $this->prepare(['date_from' => '2027-12-06', 'date_to' => '2027-12-08'],'2026-12-11')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(200, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 100 - 200 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function during_travel_and_only_once_and_period_is_2() {
        $actual = $this->prepare(['date_from' => '2027-01-11', 'date_to' => '2027-01-13'],'2025-01-11')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(400, $actual['original_price']);
        $this->assertEquals(200, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 100 - 200 / 400 * 100], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }
    
}
