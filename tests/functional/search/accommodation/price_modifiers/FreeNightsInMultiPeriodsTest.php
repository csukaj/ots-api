<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class FreeNightsInMultiPeriodsTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval) {
        return $this->prepareAccommodationSearchResult(
                $interval, 
                'Hotel I',
                [['usage' => [['age' => 21, 'amount' => 1]]]]
                );
    }

    /**
     * @test
     */
    public function it_gives_price_modifier_for_h1() {
        $actual = $this->prepare(['date_from' => '2027-05-10', 'date_to' => '2027-05-20'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(800, $actual['original_price']);
        $this->assertEquals(640, $actual['discounted_price']);
        $this->assertEquals(['value' => 160, 'percentage' => 20], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_gives_price_modifier_for_h2() {
        $actual = $this->prepare(['date_from' => '2027-07-10', 'date_to' => '2027-07-20'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1000, $actual['original_price']);
        $this->assertEquals(800, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => 20], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_gives_price_modifier_for_the_cheaper_one() {
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-15'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(720, $actual['original_price']);
        $this->assertEquals(560, $actual['discounted_price']);
        $this->assertEquals(['value' => 160, 'percentage' => 22.22], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_works_for_longer_period_of_time() {
        $actual = $this->prepare(['date_from' => '2027-06-05', 'date_to' => '2027-06-15'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(880, $actual['original_price']);
        $this->assertEquals(720, $actual['discounted_price']);
        $this->assertEquals(['value' => 160, 'percentage' => 18.18], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function No05() {
        $actual = $this->prepare(['date_from' => '2027-06-10', 'date_to' => '2027-06-15'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(480, $actual['original_price']);
        $this->assertEquals(400, $actual['discounted_price']);
        $this->assertEquals(['value' => 80, 'percentage' => 16.67], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function No06() {
        $actual = $this->prepare(['date_from' => '2027-06-10', 'date_to' => '2027-06-20'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(980, $actual['original_price']);
        $this->assertEquals(820, $actual['discounted_price']);
        $this->assertEquals(['value' => 980 - 820, 'percentage' => 16.33], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
