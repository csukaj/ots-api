<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class WeddingDuringTravelTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $wedding = null) {
        if (!$wedding) {
            $wedding = '2022-07-20';
        }
        return $this->prepareAccommodationSearchResult($interval, 'Hotel J', [['usage' => [['age' => 21, 'amount' => 2]]]], $wedding);        
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_in_the_intervallum() {
        $actual = $this->prepare(['date_from' => '2027-07-01', 'date_to' => '2027-07-06'], '2027-07-02')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(560, $actual['discounted_price']);
        $this->assertEquals(['value' => 440, 'percentage' => 44], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_at_checkin() {
        $actual = $this->prepare(['date_from' => '2027-07-01', 'date_to' => '2027-07-06'], '2027-07-01')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(560, $actual['discounted_price']);
        $this->assertEquals(['value' => 440, 'percentage' => 44], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_one_day_before_checkout() {
        $actual = $this->prepare(['date_from' => '2027-07-01', 'date_to' => '2027-07-06'], '2027-07-05')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(560, $actual['discounted_price']);
        $this->assertEquals(['value' => 440, 'percentage' => 44], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_no_price_modifier_at_checkout() {
        $actual = $this->prepare(['date_from' => '2027-07-01', 'date_to' => '2027-07-06'], '2027-07-06')['best_price'];
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
    public function it_has_no_price_modifier_in_other_month() {
        $actual = $this->prepare(['date_from' => '2026-06-01', 'date_to' => '2026-06-06'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(1000, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
