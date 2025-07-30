<?php

namespace Tests\Functional\HotelSearch\Discounts;

use Tests\TestCase;

class MergedFreeNightsFuncTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * Lásd "2017-05-16 11.28.26.jpg" 
     * "A" időszak: EUR100/éj, 5=4 kedvezmény
     * "B" időszak: EUR120/éj, 7=6 kedvezmény
     */
    private function prepare($interval) {
        return $this->prepareAccommodationSearchResult(
            $interval, "Hotel Of Merged Free Nights", [['usage' => [['age' => 21, 'amount' => 1]]]]
        );
    }

    /**
     * @test
     * 1. foglalás
     * "A" 7 éj, "B" 5 éj = 12 éj
     * első 5 nap 5=4: 1 éjszaka, EUR100 áron (1. eset)
     * második 7 nap 7=6: 1 éjszaka, EUR100 áron (2. eset)
     * kedvezmény összesen: 2 éjszaka, EUR200
     */
    public function seven_nights_in_first_period_and_five_nights_in_second() {
        $actual = $this->prepare(['date_from' => '2027-05-25', 'date_to' => '2027-06-06'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(7 * 100 + 5 * 120, $actual['original_price']);
        $this->assertEquals(7 * 100 + 5 * 120 - 200, $actual['discounted_price']);
        $this->assertEquals(200, $actual['total_discount']['value']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     * 2. foglalás
     * "A" 3 éj, "B" 9 éj = 12 éj
     * első 5 nap nincs kedvezmény: a kevésbé szigorúbb feltétel nem alkalmazható a szigorúbb időszakban!
     * második 7 nap 7=6: 1 éjszaka, EUR120 áron (1. eset)
     * kedvezmény összesen: 1 éjszaka, EUR120
     */
    public function three_nights_in_first_period_and_nine_nights_in_second() {
        $actual = $this->prepare(['date_from' => '2027-05-29', 'date_to' => '2027-06-10'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(3 * 100 + 9 * 120, $actual['original_price']);
        $this->assertEquals(3 * 100 + 9 * 120 - 120, $actual['discounted_price']);
        $this->assertEquals(120, $actual['total_discount']['value']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     * 3. foglalás
     * "A" 3 éj, "B" 4 éj = 7 éj
     * 7 nap 7=6: 1 éjszaka, EUR100 áron (2. eset)
     * kedvezmény összesen: 1 éjszaka, EUR100
     */
    public function three_nights_in_first_period_and_four_nights_in_second() {
        $actual = $this->prepare(['date_from' => '2027-05-29', 'date_to' => '2027-06-05'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(3 * 100 + 4 * 120, $actual['original_price']);
        $this->assertEquals(3 * 100 + 4 * 120 - 100, $actual['discounted_price']);
        $this->assertEquals(100, $actual['total_discount']['value']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     * 4. foglalás
     * "A" 4 éj, "B" 1 éj = 5 éj
     * nincs kedvezmény: a kevésbé szigorúbb feltétel nem alkalmazható a szigorúbb időszakban!
     * kedvezmény összesen: 0 éjszaka, EUR0
     */
    public function four_nights_in_first_period_and_one_nights_in_second() {
        $actual = $this->prepare(['date_from' => '2027-05-28', 'date_to' => '2027-06-02'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Single Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(4 * 100 + 1 * 120, $actual['original_price']);
        $this->assertEquals(4 * 100 + 1 * 120, $actual['discounted_price']);
        $this->assertEquals(0, $actual['total_discount']['value']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
