<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers\Anniversary;

use Tests\TestCase;

class AnniversaryWithMinimumNightTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval) {
        $usage = [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 2]
                        ]
                    ],
                    [
                        'usage' => [
                            ['age' => 4, 'amount' => 1],
                            ['age' => 6, 'amount' => 1]
                        ]
                    ]
                ];
        return $this->prepareAccommodationSearchResult($interval, 'Hotel Intercontinental', $usage, '2022-06-20');
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_for_2_nights() {
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-09'])['best_price'];
        $this->assertEquals(3, count($actual['devices']));
        $this->assertEquals('Classic Standard Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(600, $actual['original_price']);
        $this->assertEquals(400, $actual['discounted_price']);
        $this->assertEquals(['value' => 200, 'percentage' => round(200 / 600 * 100,2)], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
