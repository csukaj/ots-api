<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class AnnualDiscountTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    public $testData = null;

    private function prepare($interval, $accommodationName = "Hotel J", $rooms = [['usage' => [['age' => 21, 'amount' => 1]]]], $wedding = null) {
        return $this->prepareAccommodationSearchResult($interval, $accommodationName, $rooms, $wedding);
    }

    public function commonTests($expected, $actual) {
        if (!empty($expected['is_result_empty'])) {
            $this->assertEmpty($actual);
            return;
        }

        $this->assertNotEmpty($actual);
        $this->assertEquals($expected['original_price'], $actual['best_price']['original_price']);
        $this->assertEquals($expected['discounted_price'], $actual['best_price']['discounted_price']);
        $this->assertEquals($expected['total_discount']['percentage'], $actual['best_price']['total_discount']['percentage']);
        $this->assertEquals($expected['meal_plan'], $actual['best_price']['meal_plan']);
        foreach ($actual['results'][0][0]['prices'] as $price) {
            if ($price['meal_plan'] == $expected['meal_plan']) {
                $actualPrice = $price;
            }
        }
        $this->assertNotEmpty($actualPrice);
        if (!empty($expected['discounts'])) {
            $this->assertNotEmpty($actualPrice['discounts']);
            $this->assertEquals($expected['discounts'][0]['name']['en'], $actualPrice['discounts'][0]['name']['en']);
        } else {
            $this->assertEmpty($actualPrice['discounts']);
        }
    }

    /**
     * 
     * @test
     */
    public function normal_price_modifier_is_applicable_in_range() {
        $interval = ['date_from' => '2027-06-05', 'date_to' => '2027-06-11'];
        $expected = [
            'discounted_price' => 616.0,
            'original_price' => 660.0,
            'total_discount' => ['value' => 44.0, 'percentage' => 6.67,],
            'meal_plan' => 'b/b',
            'discounts' => [['name' => ['en' => 'Minimum Nights Only']]]
        ];


        $actual = $this->prepare($interval);
        $this->commonTests($expected, $actual);
    }

    /**
     * @test
     */
    public function normal_price_modifier_is_not_applicable_in_other_years() {
        $interval = ['date_from' => '2026-06-05', 'date_to' => '2026-06-11'];
        $expected = [
            'discounted_price' => 660.0,
            'original_price' => 660.0,
            'total_discount' => ['value' => 0, 'percentage' => 0],
            'meal_plan' => 'b/b',
            'discounts' => []
        ];

        $actual = $this->prepare($interval);
        $this->commonTests($expected, $actual);

        $interval = ['date_from' => '2028-06-05', 'date_to' => '2028-06-11'];

        $actual = $this->prepare($interval);
        $this->commonTests($expected, $actual);
    }

    /**
     * @test
     */
    public function annual_price_modifier_applicable_in_range_only_in_range_year() {
        $interval = ['date_from' => '2027-05-05', 'date_to' => '2027-05-11'];
        $expected = [
            'discounted_price' => 560.0,
            'original_price' => 660.0,
            'total_discount' => ['value' => 100, 'percentage' => 15.15,],
            'meal_plan' => 'b/b',
            'discounts' => [['name' => ['en' => 'Annual Minimum Nights']]]
        ];

        $actual = $this->prepare($interval);
        $this->commonTests($expected, $actual);

        $interval = ['date_from' => '2027-04-05', 'date_to' => '2027-04-11'];
        //TODO: risky, if you add discount to this range rewrite expected
        $expected = [
            'discounted_price' => 550.0,
            'original_price' => 660.0,
            'total_discount' => ['value' => 110, 'percentage' => 16.67],
            'meal_plan' => 'b/b',
            'discounts' => [['name' => ['en' => 'Free Nights for Merge']]]
        ];

        $actual = $this->prepare($interval);
        $this->commonTests($expected, $actual);

        $interval = ['date_from' => '2027-06-05', 'date_to' => '2027-06-11'];
        $expected = [
            'discounted_price' => 616.0,
            'original_price' => 660.0,
            'total_discount' => ['value' => 44.0, 'percentage' => 6.67,],
            'meal_plan' => 'b/b',
            'discounts' => [['name' => ['en' => 'Minimum Nights Only']]]
        ];

        $actual = $this->prepare($interval);
        $this->commonTests($expected, $actual);
    }

    /**
     * @test
     */
    public function annual_price_modifier_is_not_applicable_in_previous_year() {
        $interval = ['date_from' => '2026-05-05', 'date_to' => '2026-05-11'];
        $expected = [
            'discounted_price' => 660.0,
            'original_price' => 660.0,
            'total_discount' => ['value' => 0, 'percentage' => 0],
            'meal_plan' => 'b/b',
            'discounts' => []
        ];

        $actual = $this->prepare($interval);
        $this->commonTests($expected, $actual);
    }

    /**
     * @test
     */
    public function annual_price_modifier_is_applicable_in_next_years() {
        foreach (range(2028, 2032) as $year) {
            $interval = ['date_from' => $year . '-05-05', 'date_to' => $year . '-05-11'];
            $expected = [
                'discounted_price' => 560.0,
                'original_price' => 660.0,
                'total_discount' => ['value' => 100, 'percentage' => 15.15,],
                'meal_plan' => 'b/b',
                'discounts' => [['name' => ['en' => 'Annual Minimum Nights']]]
            ];

            $actual = $this->prepare($interval);
            $this->commonTests($expected, $actual);
        }
    }

}
