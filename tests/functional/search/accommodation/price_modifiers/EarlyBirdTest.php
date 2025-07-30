<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class EarlyBirdTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval) {
         return $this->prepareAccommodationSearchResult(
                $interval, 
                'Hotel J',
                [['usage' => [['age' => 21, 'amount' => 2]]]]
                );
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_with_booking_days_should_be_contained() {
        $actual = $this->prepare(['date_from' => '2027-12-01', 'date_to' => '2027-12-05'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(800, $actual['original_price']);
        $this->assertEquals(600, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 25], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_no_price_modifier_with_booking_days_should_be_contained() {
        $actual = $this->prepare(['date_from' => '2027-12-05', 'date_to' => '2027-12-15'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(2000, $actual['original_price']);
        $this->assertEquals(2000, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
