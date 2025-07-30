<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class EarlyBirdWithFixedBookingDateTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $bookingDate=null)
    {
        return $this->prepareAccommodationSearchResult(
            $interval,
            'Hotel J',
            [['usage' => [['age' => 21, 'amount' => 2]]]],
            null,
            $bookingDate
        );
    }

    /**
     * @test
     */
    public function it_has_a_priceModifier()
    {
        $actual = $this->prepare(['date_from' => '2027-09-01', 'date_to' => '2027-09-09'], '2017-11-01')['best_price'];
        $expected = [
            'devices' => [
                [
                    'id' => 25,
                    'deviceable_type' => 'App\Organization',
                    'deviceable_id' => 16,
                    'name' => ['en' => 'Comfy Room'],
                    'type' => 'room',
                    'metas' => [],
                    'classifications' => []
                ]
            ],
            'original_price' => 1600,
            'discounted_price' => 1300,
            'total_discount' => ['value' => 300, 'percentage' => 18.75],
            'meal_plan' => 'b/b'
        ];
        $this->assertEquals($expected['original_price'], $actual['original_price']);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_has_no_priceModifier()
    {
        $actual = $this->prepare(['date_from' => '2027-08-01', 'date_to' => '2027-08-09'])['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1600, $actual['original_price']);
        $this->assertEquals(1600, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
