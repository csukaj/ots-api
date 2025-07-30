<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class HoneymoonOnlyOnTheFirstRoomTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $rooms) {
        return $this->prepareAccommodationSearchResult($interval, 'Hotel J', $rooms, '2027-07-02');
    }

    /**
     * @test
     */
    public function it_has_a_price_modifier_only_on_the_first_room() {
        $actual = $this->prepare([
            'date_from' => '2027-07-01',
            'date_to' => '2027-07-07'
        ],
            [
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
            ])['best_price'];
        $this->assertEquals(3, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(3600, $actual['original_price']);
        $this->assertEquals(3160, $actual['discounted_price']);
        $this->assertEquals(['value' => 440, 'percentage' => 12.22], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
