<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class PriceRowOfferTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $organizationId = 1, $rooms = null)
    {
        if (is_null($rooms)) {
            $rooms = [['usage' => [['age' => 21, 'amount' => 1]]]];
        }
        return $this->prepareAccommodationSearchResult($interval, $organizationId, $rooms);
    }

    /**
     * @test
     */
    public function it_can_find_a_price_modifier_with_different_meal_plan_row_offer()
    {
        //discoount -> switch so original price is modified, and discounts is []
        $actual = $this->prepare(['date_from' => '2027-08-02', 'date_to' => '2027-08-09'],
            5)['results'][0][0]['prices'];
        $expected = [
            [
                'meal_plan' => 'b/b',
                'original_price' => 924,
                'discounted_price' => 924,
                'discounts' => [],
                'order_itemable_index' => 0,
                'meal_plan_id' => '2'
            ],
            [
                'meal_plan' => 'h/b',
                'original_price' => 924,
                'discounted_price' => 924,
                'discounts' => [],
                'order_itemable_index' => 0,
                'meal_plan_id' => '3'
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_find_a_price_modifier_with_different_meal_plan_row_offer_MEAL_PLAN_DOWNGRADE()
    {
        //discoount -> switch so original price is modified, and discounts is []
        $actual = $this->prepare(['date_from' => '2027-08-16', 'date_to' => '2027-08-23'],
            5)['results'][0][0]['prices'];
        $expected = [
            [
                'meal_plan' => 'b/b',
                'original_price' => 1008,
                'discounted_price' => 1008,
                'discounts' => [],
                'order_itemable_index' => 0,
                'meal_plan_id' => '2'
            ],
            [
                'meal_plan' => 'h/b',
                'original_price' => 1008,
                'discounted_price' => 1008,
                'discounts' => [],
                'order_itemable_index' => 0,
                'meal_plan_id' => '3'
            ]

        ];
        $this->assertEquals($expected, $actual);
    }


    /**
     * @test
     */
    public function it_can_find_a_simple_product_row_switch()
    {
        //switch modifies original price and dont' apperar as discount
        $actual = $this->prepare(['date_from' => '2027-09-03', 'date_to' => '2027-09-15'],
            1)['results'][0][0]['prices'];
        $expected = [
            [
                'original_price' => '1056.0',
                'meal_plan_id' => 2,
                'discounted_price' => '1056.0',
                'order_itemable_index' => 0,
                'meal_plan' => 'b/b',
                'discounts' => [
                    [
                        'name' => [
                            'en' => 'Textual Offer',
                            'ru' => 'Текстология предложение',
                            'de' => 'Textangebot',
                            'hu' => 'Szöveges Ajánlat'
                        ],
                        'discount_percentage' => 0.0,
                        'offer' => 'textual',
                        'discount_value' => 0,
                        'condition' => 'long_stay',
                        'modifier_type' => 491,
                        'description' => [
                            'ru' => 'Это текстуальное предложение',
                            'de' => 'Dies ist ein Textangebot',
                            'en' => 'This is a textual offer',
                            'hu' => 'Ez egy szöveges ajánlat'

                        ],
                    ],
                ],
                'order_itemable_index' => 0,
                'meal_plan_id' => '2',
                'total_discount' => ['percentage' => 0.0, 'value' => 0],
            ],
            [
                'original_price' => '1069.2',
                'meal_plan_id' => 3,
                'discounted_price' => '1069.2',
                'order_itemable_index' => 0,
                'meal_plan' => 'h/b',
                'discounts' => [
                    [
                        'name' => [
                            'en' => 'Textual Offer',
                            'ru' => 'Текстология предложение',
                            'de' => 'Textangebot',
                            'hu' => 'Szöveges Ajánlat'
                        ],
                        'discount_percentage' => 0.0,
                        'offer' => 'textual',
                        'discount_value' => 0.0,
                        'modifier_type' => 491,
                        'condition' => 'long_stay',
                        'description' => [
                            'ru' => 'Это текстуальное предложение',
                            'de' => 'Dies ist ein Textangebot',
                            'en' => 'This is a textual offer',
                            'hu' => 'Ez egy szöveges ajánlat'

                        ],
                    ],
                ],
                'order_itemable_index' => 0,
                'meal_plan_id' => '3',
                'total_discount' => ['percentage' => 0, 'value' => 0],
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_find_a_product_row_price_modifier_when_all_price_modifier_product_row_exists()
    {
        $actual = $this->prepare(['date_from' => '2027-09-03', 'date_to' => '2027-09-15'],
            1)['results'][0][0]['prices'];
        $expected = [
            [
                'original_price' => '1056.0',
                'meal_plan_id' => '2',
                'discounted_price' => '1056.0',
                'order_itemable_index' => 0,
                'meal_plan' => 'b/b',
                'discounts' => [
                    [
                        'name' => [
                            'en' => 'Textual Offer',
                            'ru' => 'Текстология предложение',
                            'de' => 'Textangebot',
                            'hu' => 'Szöveges Ajánlat'
                        ],
                        'discount_percentage' => 0.0,
                        'offer' => 'textual',
                        'discount_value' => 0.0,
                        'modifier_type' => 491,
                        'condition' => 'long_stay',
                        'description' => [
                            'ru' => 'Это текстуальное предложение',
                            'de' => 'Dies ist ein Textangebot',
                            'en' => 'This is a textual offer',
                            'hu' => 'Ez egy szöveges ajánlat'

                        ],
                    ],
                ],
                'order_itemable_index' => 0,
                'meal_plan_id' => '2',
                'total_discount' => ['percentage' => 0.0, 'value' => 0]
            ],
            [
                'original_price' => '1069.2',
                'meal_plan_id' => '3',
                'discounted_price' => '1069.2',
                'order_itemable_index' => 0,
                'meal_plan' => 'h/b',
                'discounts' => [
                    [
                        'name' => [
                            'en' => 'Textual Offer',
                            'ru' => 'Текстология предложение',
                            'de' => 'Textangebot',
                            'hu' => 'Szöveges Ajánlat'
                        ],
                        'discount_percentage' => 0.0,
                        'offer' => 'textual',
                        'discount_value' => 0.0,
                        'modifier_type' => 491,
                        'condition' => 'long_stay',
                        'description' => [
                            'ru' => 'Это текстуальное предложение',
                            'de' => 'Dies ist ein Textangebot',
                            'en' => 'This is a textual offer',
                            'hu' => 'Ez egy szöveges ajánlat'

                        ],
                    ]
                ],
                'order_itemable_index' => 0,
                'meal_plan_id' => '3',
                'total_discount' => ['percentage' => 0, 'value' => 0]
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_find_a_price_without_price_modifier_when_product_rows_missing()
    {
        // in case of no price row discount present, just calculate with the same price
        $usages = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 2],
                    ['age' => 5, 'amount' => 1]
                ]
            ]
        ];

        $actual = $this->prepare(['date_from' => '2027-09-03', 'date_to' => '2027-09-15'], 1,
            $usages)['results'][0][0]['prices'];
        $expected = [
            [
                'original_price' => '2460.0',
                'meal_plan_id' => '2',
                'discounted_price' => '2460.0',
                'order_itemable_index' => 0,
                'meal_plan' => 'b/b',
                'discounts' =>
                    [
                        [
                            'name' => [
                                'ru' => 'Текстология предложение',
                                'de' => 'Textangebot',
                                'en' => 'Textual Offer',
                                'hu' => 'Szöveges Ajánlat'
                            ],
                            'discount_percentage' => 0.0,
                            'offer' => 'textual',
                            'discount_value' => 0,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => [
                                'ru' => 'Это текстуальное предложение',
                                'de' => 'Dies ist ein Textangebot',
                                'en' => 'This is a textual offer',
                                'hu' => 'Ez egy szöveges ajánlat'
                            ],
                        ],
                    ],
                'total_discount' => ['percentage' => 0.0, 'value' => 0],
            ],
            [
                'original_price' => '2460.0',
                'meal_plan_id' => '3',
                'discounted_price' => '2460.0',
                'order_itemable_index' => 0,
                'meal_plan' => 'h/b',
                'discounts' =>
                    [
                        [
                            'name' => [
                                'ru' => 'Текстология предложение',
                                'de' => 'Textangebot',
                                'en' => 'Textual Offer',
                                'hu' => 'Szöveges Ajánlat'
                            ],
                            'discount_percentage' => 0.0,
                            'offer' => 'textual',
                            'discount_value' => 0,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => [
                                'ru' => 'Это текстуальное предложение',
                                'de' => 'Dies ist ein Textangebot',
                                'en' => 'This is a textual offer',
                                'hu' => 'Ez egy szöveges ajánlat'
                            ],
                        ],
                    ],
                'total_discount' => ['percentage' => 0.0, 'value' => 0],
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

}
