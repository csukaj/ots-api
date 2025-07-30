<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class LongStayPercentageWithDeductionBaseUsageTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval) {
        return $this->prepareAccommodationSearchResult(
                $interval, 
                'Hotel D',
                [['usage' => [['age' => 21, 'amount' => 1]]]]
                );
    }

    /**
     * @test
     */
    public function it_has_correct_prices_without_priceModifier() {
        $actual = $this->prepare(['date_from' => '2026-07-05', 'date_to' => '2026-07-09'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(528, $actual['original_price']);
        $this->assertEquals(528, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('f/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_has_correct_prices_with_priceModifier() {
        $actual = $this->prepare(['date_from' => '2027-07-05', 'date_to' => '2027-07-09'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(528, $actual['original_price']);
        $this->assertEquals(475.2, $actual['discounted_price']);
        $this->assertEquals(['value' => 52.8, 'percentage' => 10], $actual['total_discount']);
        $this->assertEquals('f/b', $actual['meal_plan']);
    }

}
