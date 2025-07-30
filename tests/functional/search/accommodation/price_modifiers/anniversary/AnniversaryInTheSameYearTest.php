<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers\Anniversary;

use Tests\TestCase;

class AnniversaryInTheSameYearTest extends TestCase {

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
    public function it_has_a_price_modifier_in_the_anniversary_year() {
        foreach (['2022-07-20', '2017-01-22'] as $wedding) {
            $actual = $this->prepare(['date_from' => '2027-12-08', 'date_to' => '2027-12-13'], $wedding)['best_price'];
            $this->assertEquals(1, count($actual['devices']));
            $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
            $this->assertEquals(1000, $actual['original_price']);
            $this->assertEquals(800, $actual['discounted_price']);
            $this->assertEquals(['value' => 200, 'percentage' => 20], $actual['total_discount']);
            $this->assertEquals('b/b', $actual['meal_plan']);
        }
    }

    /**
     * @test
     */
    public function it_has_no_price_modifier_in_other_year() {
        foreach (range(2023, 2026) as $y) {
            $actual = $this->prepare(['date_from' => '2027-12-08', 'date_to' => '2027-12-13'], $y . '-07-20')['best_price'];
            $this->assertEquals(1, count($actual['devices']));
            $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
            $this->assertEquals(1000, $actual['original_price']);
            $this->assertEquals(1000, $actual['discounted_price']);
            $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
            $this->assertEquals('b/b', $actual['meal_plan']);
        }
    }

    /**
     * @test
     */
    public function it_has_no_price_modifier_in_the_same_year_of_anniversary() {
        $actual = $this->prepare(['date_from' => '2027-12-08', 'date_to' => '2027-12-13'], '2027-07-20')['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(1000, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
