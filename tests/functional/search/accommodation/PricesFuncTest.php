<?php

namespace Tests\Functional\Search\Accommodation;

use App\Entities\Search\AccommodationSearchEntity;
use App\Organization;
use Tests\TestCase;

class PricesFuncTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_calculate_best_price()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendDataList = $accommodationSearchEntity->setParameters([
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendData = $accommodationSearchEntity->setParameters(
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
        )->getFrontendData()[1]['results'][0];

        $devices = Organization::find(1)->devices;
        $deviceIds = [];
        for ($i = 0; $i < count($devices); $i++) {
            $deviceIds[] = $devices[$i]->id;
        }

        $results = [
            [
                'device_id' => $deviceIds[0],
                'usages' => [
                    'adult' => 1
                ],
                'prices' => [
                    [
                        'discounted_price' => 1100,
                        'original_price' => 1100,
                        'meal_plan_id' => 2,
                        'meal_plan' => 'b/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ],
                    [
                        'discounted_price' => 1111,
                        'original_price' => 1111,
                        'meal_plan_id' => 3,
                        'meal_plan' => 'h/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ]
                ]
            ],
            [
                'device_id' => $deviceIds[1],
                'usages' => [
                    'adult' => 1
                ],
                'prices' => [
                    [
                        'discounted_price' => 1210,
                        'original_price' => 1210,
                        'meal_plan_id' => 2,
                        'meal_plan' => 'b/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ],
                    [
                        'discounted_price' => 1210,
                        'original_price' => 1210,
                        'meal_plan_id' => 3,
                        'meal_plan' => 'h/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ]
                ]
            ],
            [
                'device_id' => $deviceIds[2],
                'usages' => [
                    'adult' => 1
                ],
                'prices' => [
                    [
                        'discounted_price' => 2750,
                        'original_price' => 2750,
                        'meal_plan_id' => 2,
                        'meal_plan' => 'b/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ],
                    [
                        'discounted_price' => 2750,
                        'original_price' => 2750,
                        'meal_plan_id' => 3,
                        'meal_plan' => 'h/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ]
                ]
            ]
        ];


        $this->assertCount(3, $frontendData);
        $this->assertEquals($results, $frontendData);
    }

    /**
     * @test
     */
    function it_can_get_prices_from_two_date_ranges()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendData = $accommodationSearchEntity->setParameters(
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
        )->getFrontendData()[1]['results'];

        $expected =
            [
                'device_id' => Organization::find(1)->devices[0]->id,
                'usages' => [
                    'adult' => 1
                ],
                'prices' => [
                    [
                        'discounted_price' => 778.8,
                        'original_price' => 778.8,
                        'meal_plan_id' => 2,
                        'meal_plan' => 'b/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ],
                    [
                        'discounted_price' => 786.5,
                        'original_price' => 786.5,
                        'meal_plan_id' => 3,
                        'meal_plan' => 'h/b',
                        'discounts' => [],
                        'order_itemable_index' => 0
                    ]

                ]
            ];
        $this->assertCount(1, $frontendData);
        $this->assertCount(3, $frontendData[0]);
        $this->assertEquals($expected, $frontendData[0][0]);
    }

    /**
     * @test
     */
    function it_cannot_get_calculation_when_price_is_missing()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendDataList = $accommodationSearchEntity->setParameters(
            [
                'organizations' => [8],
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
        $frontendFullData = (new AccommodationSearchEntity())->setParameters(
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
        $frontendDataList = $frontendFullData[1]['results'][0][0]['prices'];
        $this->assertEquals(
            [
                [
                    'discounted_price' => '1100.0',
                    'original_price' => '1100.0',
                    'discounts' => [],
                    'meal_plan' => 'b/b',
                    'meal_plan_id' => 2,
                    'order_itemable_index' => 0
                ],
                [
                    'discounted_price' => '1111.0',
                    'original_price' => '1111.0',
                    'discounts' => [],
                    'meal_plan' => 'h/b',
                    'meal_plan_id' => 3,
                    'order_itemable_index' => 0
                ]

            ], $frontendDataList);
    }

    /**
     * @test
     */
    function it_can_get_prices_with_price_modifiers()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendDataList = $accommodationSearchEntity->setParameters(
            [
                'organizations' => [1],
                'interval' => [
                    'date_from' => '2027-07-03',
                    'date_to' => '2027-07-09'
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

        $deviceId = Organization::find(1)->devices[0]->id;

        $expected = [
            'device_id' => $deviceId,
            'usages' => [
                'adult' => 1
            ],
            'prices' => [
                [
                    'original_price' => '660.0',
                    'discounted_price' => '495.0',
                    'meal_plan_id' => '2',
                    'meal_plan' => 'b/b',
                    'discounts' => [
                        [
                            'name' => ['en' => 'Free Nights Offer'],
                            'discount_percentage' => -16.67,
                            'offer' => 'free_nights',
                            'discount_value' => -110,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => null
                        ],
                        [
                            'name' => ['en' => 'Long Stay Percentage Based On B/B Price'],
                            'discount_percentage' => -8.33,
                            'offer' => 'percentage',
                            'discount_value' => -55,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => null
                        ],
                        [
                            'name' => [
                                'de' => 'Textangebot',
                                'en' => 'Textual Offer',
                                'hu' => 'Szöveges Ajánlat',
                                'ru' => 'Текстология предложение',
                            ],
                            'discount_percentage' => 0,
                            'offer' => 'textual',
                            'discount_value' => 0,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => [
                                'de' => 'Dies ist ein Textangebot',
                                'en' => 'This is a textual offer',
                                'hu' => 'Ez egy szöveges ajánlat',
                                'ru' => 'Это текстуальное предложение'
                            ]
                        ]
                    ],
                    'total_discount' => [
                        'percentage' => -25,
                        'value' => -165,
                    ],
                    'order_itemable_index' => 0
                ],
                [
                    'original_price' => '679.8',
                    'discounted_price' => '511.83',
                    'meal_plan_id' => '3',
                    'meal_plan' => 'h/b',
                    'discounts' => [
                        [
                            'name' => ['en' => 'Free Nights Offer'],
                            'discount_percentage' => -16.67,
                            'offer' => 'free_nights',
                            'discount_value' => -113.3,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => null
                        ],
                        [
                            'name' => ['en' => 'Long Stay Percentage Based On B/B Price'],
                            'discount_percentage' => -8.04,
                            'offer' => 'percentage',
                            'discount_value' => -54.67,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => null
                        ],
                        [
                            'name' => [
                                'de' => 'Textangebot',
                                'en' => 'Textual Offer',
                                'hu' => 'Szöveges Ajánlat',
                                'ru' => 'Текстология предложение',
                            ],
                            'discount_percentage' => 0,
                            'offer' => 'textual',
                            'discount_value' => 0,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => [
                                'de' => 'Dies ist ein Textangebot',
                                'en' => 'This is a textual offer',
                                'hu' => 'Ez egy szöveges ajánlat',
                                'ru' => 'Это текстуальное предложение'
                            ]
                        ]
                    ],
                    'total_discount' => [
                        'percentage' => -24.71,
                        'value' => -167.97,
                    ],
                    'order_itemable_index' => 0
                ]
            ]
        ];

        $actual = $frontendDataList[1]['results'][0][0];

        $this->assertEquals($expected, $actual);
    }


}
