<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class DeductionBasePriceTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    private $adult_price = 132;
    private $child_price = 142;

    private function prepare($rooms = null) {
        $interval = ['date_from' => '2027-09-23', 'date_to' => '2027-09-24'];
        return $this->prepareAccommodationSearchResult($interval, 3, $rooms)['best_price'];
    }

    private function commonTests($expected, $actual) {
        $this->assertEquals($expected['original_price'], $actual['original_price']);
        $this->assertEquals($expected['discount_value'], $actual['original_price'] - $actual['discounted_price']);
    }

    private function calcExpectedPrice($rooms) {
        $sum = 0;
        foreach ($rooms[0]['usage'] as $uitem) {
            $sum += $uitem['amount'] * ($uitem['age'] == 21 ? $this->adult_price : $this->child_price);
        }
        return $sum;
    }

    /**
     * @test
     */
    public function discount_for_1a() {
        /** prices =  A:132, +A: 132, +C 142
         * +-------------+
         * | A | +A | +C |
         * ---------------
         * | 1 |  0 |  0 | <= usage
         * | 1 |  1 |  2 | <= deduction base prices
         * ===============
         * | 1 |  0 |  0 | <= used for discount
         * ===============
         */
        $rooms = [
            ['usage' => [['age' => 21, 'amount' => 1]]]
        ];
        $expected = [
            'original_price' => $this->calcExpectedPrice($rooms),
            'discount_value' => $this->adult_price * 1 * 0.1
        ];

        $this->commonTests($expected, $this->prepare($rooms));
    }

    /**
     * @test
     */
    public function discount_for_2a() {
        /** prices =  A:132, +A: 132, +C 142
         * +-------------+
         * | A | +A | +C |
         * ---------------
         * | 1 |  1 |  0 | <= usage
         * | 1 |  1 |  2 | <= deduction base prices
         * ===============
         * | 1 |  1 |  0 | <= used for discount
         * ===============
         */
        $rooms = [
            ['usage' => [['age' => 21, 'amount' => 2]]]
        ];
        $expected = [
            'original_price' => $this->calcExpectedPrice($rooms),
            'discount_value' => $this->adult_price * 2 * 0.1
        ];

        $this->commonTests($expected, $this->prepare($rooms));
    }

    /**
     * @test
     */
    public function discount_for_3a() {
        /** prices =  A:132, +A: 132, +C 142
         * +-------------+
         * | A | +A | +C |
         * ---------------
         * | 1 |  2 |  0 | <= usage
         * | 1 |  1 |  2 | <= deduction base prices
         * ===============
         * | 1 |  1 |  0 | <= used for discount
         * ===============
         */
        $rooms = [
            ['usage' => [['age' => 21, 'amount' => 3]]]
        ];
        $expected = [
            'original_price' => $this->calcExpectedPrice($rooms),
            'discount_value' => $this->adult_price * 2 * 0.1
        ];

        $this->commonTests($expected, $this->prepare($rooms));
    }
    

    /**
     * @test
     */
    public function discount_for_1a_1c() {
        /** prices =  A:132, +A: 132, +C 142
         * +-------------+
         * | A | +A | +C |
         * ---------------
         * | 1 |  0 |  1 | <= usage
         * | 1 |  1 |  2 | <= deduction base prices
         * ===============
         * | 1 |  0 |  1 | <= used for discount
         * ===============
         */
        $rooms = [
            ['usage' => [['age' => 21, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        $expected = [
            'original_price' => $this->calcExpectedPrice($rooms),
            'discount_value' => ($this->adult_price * 1 + $this->child_price * 1) * 0.1
        ];

        $this->commonTests($expected, $this->prepare($rooms));
    }

    /**
     * @test
     */
    public function discount_for_1a_2c() {
        /** prices =  A:132, +A: 132, +C 142
         * +-------------+
         * | A | +A | +C |
         * ---------------
         * | 1 |  0 |  2 | <= usage
         * | 1 |  1 |  2 | <= deduction base prices
         * ===============
         * | 1 |  0 |  2 | <= used for discount
         * ===============
         */
        $rooms = [
            ['usage' => [['age' => 21, 'amount' => 1], ['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        $expected = [
            'original_price' => $this->calcExpectedPrice($rooms),
            'discount_value' => ($this->adult_price * 1 + $this->child_price * 2) * 0.1
        ];

        $this->commonTests($expected, $this->prepare($rooms));
    }

    /**
     * @test
     */
    public function discount_for_1a_3c() {
        /** prices =  A:132, +A: 132, +C 142
         * +-------------+
         * | A | +A | +C |
         * ---------------
         * | 1 |  0 |  3 | <= usage
         * | 1 |  1 |  2 | <= deduction base prices
         * ===============
         * | 1 |  0 |  2 | <= used for discount
         * ===============
         */
        $rooms = [
            ['usage' => [['age' => 21, 'amount' => 1], ['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        $expected = [
            'original_price' => $this->calcExpectedPrice($rooms),
            'discount_value' => ($this->adult_price * 1 + $this->child_price * 2) * 0.1
        ];

        $this->commonTests($expected, $this->prepare($rooms));
    }

    /**
     * @test
     */
    public function discount_for_3a_3c() {
        /** prices =  A:132, +A: 132, +C 142
         * +-------------+
         * | A | +A | +C |
         * ---------------
         * | 1 |  2 |  3 | <= usage
         * | 1 |  1 |  2 | <= deduction base prices
         * ===============
         * | 1 |  1 |  2 | <= used for discount
         * ===============
         */
        $rooms = [
            ['usage' => [['age' => 21, 'amount' => 3], ['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        $expected = [
            'original_price' => $this->calcExpectedPrice($rooms),
            'discount_value' => ($this->adult_price * 2 + $this->child_price * 2) * 0.1
        ];

        $this->commonTests($expected, $this->prepare($rooms));
    }

}
