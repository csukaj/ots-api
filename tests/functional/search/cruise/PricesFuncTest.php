<?php

namespace Tests\Functional\Search\Cruise;

use App\Entities\Search\CruiseSearchEntity;
use App\Organization;
use App\OrganizationGroup;
use App\ShipGroup;
use Tests\TestCase;

class PricesFuncTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_calculate_best_price()
    {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendDataList = $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2027-07-06',
                'date_to' => '2027-07-09'
            ],
            'usages' => [['usage' => [['age' => 21, 'amount' => 1]]]]
        ])->getFrontendData();

        foreach ($frontendDataList as $frontendData) {

            $this->assertTrue(isset($frontendData['best_price']));
            if (!empty($frontendData['best_price'])) {
                $bp = $frontendData['best_price'];
                $this->assertTrue(isset($bp['discounted_price']));
                $this->assertTrue(is_numeric($bp['discounted_price']));
                $this->assertTrue(isset($bp['total_discount']));
                $this->assertTrue(isset($bp['original_price']));
                //$this->assertTrue(isset($bp['discounts']));
            }
        }
    }

    /**
     * @test
     */
    function it_can_get_prices()
    {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendData = $cruiseSearchEntity->setParameters(
            [
                'organizations' => [1],
                'interval' => [
                    'date_from' => '2026-06-03',
                    'date_to' => '2026-06-13'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        )->getFrontendData()[0]['results'][0];

        $results = [
            [
                'device_id' => 42,
                'usages' => [
                    'adult' => 1
                ],
                'prices' => [
        [
            'discounted_price' => '440.0',
            'original_price' => '440.0',
            'meal_plan_id' => '1',
            'meal_plan' => 'e/p',
            'discounts' => [],
            'order_itemable_index' => 0
        ],
        [
            'discounted_price' => '800.0',
            'original_price' => '800.0',
            'meal_plan_id' => '4',
            'meal_plan' => 'f/b',
            'discounts' => [],
            'order_itemable_index' => 0
        ]
    ]

            ],
            [
                'device_id' => 43,
                'usages' => [
                    'adult' => 1
                ],
                'prices' => [
                    [
                        'discounted_price' => '492.0',
                        'original_price' => '492.0',
                        'meal_plan_id' => '1',
                        'meal_plan' => 'e/p',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ],
                    [
                        'discounted_price' => '844.0',
                        'original_price' => '844.0',
                        'meal_plan_id' => '4',
                        'meal_plan' => 'f/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ]
                ]
            ]
        ];

        $this->assertCount(2, $frontendData);
        $this->assertEquals($results, $frontendData);
    }

    /**
     * @test
     */
    function it_can_get_prices_from_two_date_ranges()
    {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendData = $cruiseSearchEntity->setParameters(
            [
                'organizations' => [1],
                'interval' => [
                    'date_from' => '2026-08-30',
                    'date_to' => '2026-09-06'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        )->getFrontendData()[0]['results'];

        $expected =
            [
                'device_id' => ShipGroup::find(1)->devices[0]->id,
                'usages' => [
                    'adult' => 1
                ],
                'prices' => [
                    [
                        'discounted_price' => '420.0',
                        'original_price' => '420.0',
                        'meal_plan_id' => '1',
                        'meal_plan' => 'e/p',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ],
                    [
                        'discounted_price' => '780.0',
                        'original_price' => '780.0',
                        'meal_plan_id' => '4',
                        'meal_plan' => 'f/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ]

                ]
            ];
        $this->assertCount(1, $frontendData);
        $this->assertCount(2, $frontendData[0]);
        $this->assertEquals($expected, $frontendData[0][0]);
    }

    /**
     * @test
     */
    function it_cannot_get_calculation_when_price_is_missing()
    {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendDataList = $cruiseSearchEntity->setParameters(
            [
                'interval' => [
                    'date_from' => '2026-01-30',
                    'date_to' => '2026-02-03'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        )->getFrontendData();
        $this->assertEmpty($frontendDataList);
    }

    /**
     * @test
     */
    function it_can_get_prices_with_wedding_date()
    {
        $frontendFullData = (new CruiseSearchEntity())->setParameters(
            [
                'organizations' => [1],
                'interval' => [
                    'date_from' => '2026-06-20',
                    'date_to' => '2026-06-30' // 4 nights
                ],
                'wedding_date' => '2012-08-11',
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        )->getFrontendData();
        $frontendDataList = $frontendFullData[0]['results'][0][0]['prices'];
        $this->assertEquals(
            [
                [
                    'discounted_price' => '440.0',
                    'original_price' => '440.0',
                    'discounts' => [],
                    'meal_plan' => 'e/p',
                    'meal_plan_id' => 1,
                    'order_itemable_index' => 0
                ],
                [
                    'discounted_price' => '800.0',
                    'original_price' => '800.0',
                    'discounts' => [],
                    'meal_plan' => 'f/b',
                    'meal_plan_id' => '4',
                    'order_itemable_index' => 0
                ]
            ], $frontendDataList);
    }

    /**
     * @test
     */
    function it_can_get_prices_with_price_modifiers()
    {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendDataList = $cruiseSearchEntity->setParameters(
            [
                'organizations' => [1],
                'interval' => [
                    'date_from' => '2027-06-01',
                    'date_to' => '2027-06-15'
                ],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ]
                ]
            ]
        )->getFrontendData();
        $this->assertEquals([
            [
                'original_price' => '440.0',
                'order_itemable_index' => 0,
                'discounted_price' => '396.0',
                'meal_plan' => 'e/p',
                'discounts' => [
                    [
                        'name' => [
                            'en' => 'Minimum Nights Only',
                            'hu' => 'Csak minimum éjszaka',
                        ],
                        'discount_percentage' => -10,
                        'offer' => 'fixed_price',
                        'discount_value' => -44.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
                'meal_plan_id' => '1',
                'total_discount' => [
                    'percentage' => -10,
                    'value' => -44.0,
                ],
            ],
            [
                'original_price' => '800.0',
                'order_itemable_index' => 0,
                'discounted_price' => '756.0',
                'meal_plan' => 'f/b',
                'discounts' => [
                    [
                        'name' => [
                            'en' => 'Minimum Nights Only',
                            'hu' => 'Csak minimum éjszaka',
                        ],
                        'discount_percentage' => -5.5,
                        'offer' => 'fixed_price',
                        'discount_value' => -44.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
                'meal_plan_id' => '4',
                'total_discount' => [
                    'percentage' => -5.5,
                    'value' => -44.0,
                ],
            ],
        ], $frontendDataList[0]['results'][0][0]['prices']);
    }


}
