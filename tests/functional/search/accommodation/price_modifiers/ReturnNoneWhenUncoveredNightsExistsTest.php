<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class ReturnNoneWhenUncoveredNightsExistsTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval) {
        return $this->prepareAccommodationSearchResult($interval, 'Hotel J', [['usage' => [['age' => 21, 'amount' => 1]]]]);
    }

    /**
     * @test
     */
    public function it_has_a_priceModifier() {
        $actual = $this->prepare(['date_from' => '2027-06-06', 'date_to' => '2027-06-10'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(440, $actual['original_price']);
        $this->assertEquals(396, $actual['discounted_price']);
        $this->assertEquals(['value' => 44, 'percentage' => 10], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_no_priceModifier() {
        $actual = $this->prepare(['date_from' => '2027-06-06', 'date_to' => '2027-06-09'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(330, $actual['original_price']);
        $this->assertEquals(330, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
