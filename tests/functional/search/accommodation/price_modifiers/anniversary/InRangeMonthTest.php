<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers\Anniversary;

use Tests\TestCase;

class InRangeMonthTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $wedding = null) {
        if (!$wedding) {
            $wedding = '2022-11-20';
        }
        return $this->prepareAccommodationSearchResult($interval, 'Hotel Bride', [['usage' => [['age' => 21, 'amount' => 2]]]], $wedding);
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_in_range_for_anniversary_in_range_months() {
        $actual = $this->prepare(['date_from' => '2027-02-02', 'date_to' => '2027-02-07'], '2022-01-05')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(700, $actual['discounted_price']);
        $this->assertEquals(['value' => 300, 'percentage' => 30], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function no_price_modifier_out_of_range_for_anniversary_in_range_months() {
        $actual = $this->prepare(['date_from' => '2027-02-03', 'date_to' => '2027-02-08'], '2022-01-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(1000, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_in_range_for_wedding_in_less_than_months() {
        $actual = $this->prepare(['date_from' => '2027-02-12', 'date_to' => '2027-02-17'], '2027-01-15')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(700, $actual['discounted_price']);
        $this->assertEquals(['value' => 300, 'percentage' => 30], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function no_price_modifier_out_of_range_for_wedding_in_less_than_months() {
        $actual = $this->prepare(['date_from' => '2027-02-12', 'date_to' => '2027-02-17'], '2027-01-05')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(1000, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
