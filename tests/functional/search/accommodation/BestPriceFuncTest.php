<?php

namespace Tests\Functional\Search\Accommodation;

use App\Organization;
use Tests\TestCase;

class BestPriceFuncTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    public function it_returns_all_expected_keys()
    {
        $actual = $this->prepareAccommodationSearchResult(
            ['date_from' => '2026-06-02', 'date_to' => '2026-06-08'],
            1,
            [['usage' => [['age' => 21, 'amount' => 1]]]]
        )['best_price'];

        $expected = [
            'discounted_price' => 660.0,
            'original_price' => 660.0,
            'total_discount' => [
                'value' => 0.0,
                'percentage' => 0.0,
            ],
            'devices' => [
                [
                    'id' => 1306,
                    'deviceable_id' => 1,
                    'deviceable_type' => Organization::class,
                    'name' => [
                        'en' => 'Single Room',
                    ],
                    'type' => 'room',
                    'metas' => [],
                    'classifications' => []
                ]
            ],
            'meal_plan' => null,
        ];
        $this->assertEquals($expected['discounted_price'], $actual['discounted_price']);
        $this->assertEquals($expected['original_price'], $actual['original_price']);
        $this->assertEquals($expected['total_discount'], $actual['total_discount']);
        $this->assertEquals(1, count($expected['devices']));
        $this->assertEquals($expected['devices'][0]['deviceable_id'], $actual['devices'][0]['deviceable_id']);
        $this->assertEquals($expected['devices'][0]['deviceable_type'], $actual['devices'][0]['deviceable_type']);
        $this->assertEquals($expected['devices'][0]['name'], $actual['devices'][0]['name']);
        $this->assertEquals($expected['devices'][0]['type'], $actual['devices'][0]['type']);
    }

    /**
     * @test
     */
    public function it_returns_empty_if_price_is_zero()
    {
        $organization = TestCase::getOrganizationsByName('Hotel F')[0];
        $actual = $this->prepareAccommodationSearchResult(
            ['date_from' => '2026-07-28', 'date_to' => '2026-08-03'],
            null,
            [['usage' => [['age' => 21, 'amount' => 1]]]]
        );
        $this->assertFalse(in_array($organization->id, array_keys($actual)));
    }

}
