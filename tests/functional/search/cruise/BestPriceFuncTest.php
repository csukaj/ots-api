<?php

namespace Tests\Functional\Search\Cruise;

use App\Organization;
use App\OrganizationGroup;
use App\ShipGroup;
use Tests\TestCase;

class BestPriceFuncTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    public function it_returns_all_expected_keys()
    {
        $actual = $this->prepareCruiseSearchResult(
            ['date_from' => '2026-06-01', 'date_to' => '2026-06-08'],
            1,
            [['usage' => [['age' => 21, 'amount' => 1]]]]
        )[0]['best_price'];

        $expected = [
            'discounted_price' => 440.0,
            'original_price' => 440.0,
            'total_discount' => [
                'value' => 0.0,
                'percentage' => 0.0,
            ],
            'devices' => [
                [
                    'id' => 42,
                    'deviceable_id' => 1,
                    'deviceable_type' => ShipGroup::class,
                    'name' => [
                        'en' => 'Single Cabin',
                    ],
                    'type' => 'cabin',
                    'metas' => [],
                    'classifications' => []
                ]
            ],
            'meal_plan' => 'e/p',
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_returns_empty_if_price_is_zero()
    {
        $organization = TestCase::getOrganizationsByName('Hotel F')[0];
        $actual = $this->prepareCruiseSearchResult(
            ['date_from' => '2026-07-28', 'date_to' => '2026-08-03'],
            null,
            [['usage' => [['age' => 21, 'amount' => 1]]]]
        );
        $this->assertFalse(in_array($organization->id, array_keys($actual)));
    }

}
