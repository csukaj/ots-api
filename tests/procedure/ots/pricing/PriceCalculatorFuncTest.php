<?php

namespace Tests\Procedure\Ots\Pricing;

use Tests\Procedure\ProcedureTestCase;

class PriceCalculatorFuncTest extends ProcedureTestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.pricing.price_calculator import PriceCalculator' . PHP_EOL .
                              'import json' . PHP_EOL ;

    private function prepareAndRun($script, $organizationId = 1, $deviceIndex = 0, $fromDate = '2026-06-02', $toDate = '2026-06-08', $secondaryCalculation = 'False', $deductionBasePrices='None') {
        $config = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => $fromDate . ' 12:00:00',
            'to_time' => $toDate . ' 12:00:00',
            'productable_type' => 'App\\\\Device',
            'productable_id' => $this->scriptContainer("factory.device('App\\Organization', {$organizationId}, {$deviceIndex})['id']"),
            'price_modifiable_type' => 'App\\\\Organization',
            'price_modifiable_id' => $organizationId,
            'date_ranges' => $this->scriptContainer("{62:factory.open_date_ranges({$organizationId})}"),
            'deduction_calculation' => $this->scriptContainer($secondaryCalculation),
            'age_resolver' => $this->scriptContainer("factory.age_resolver('App\\\\Organization', {$organizationId})"),
            'deduction_base_prices' => $this->scriptContainer($deductionBasePrices)
        ]);
        return $this->runPythonScript(self::$imports . "price_calculator = PriceCalculator({$config})" . PHP_EOL . $script);
    }

    /**
     * @test
     */
    public function it_can_get_offers()
    {
        $actual = $this->jsonDecode(
            $this->prepareAndRun('
import json
print json.dumps(price_calculator.get_offers(factory.room_request(usage_elements=None)))
            '), true
        );
        $expected = [
            'room_request' => [
                'usage' => [
                    [
                        'age' => 21,
                        'amount' => 1,
                    ],
                ],
            ],
            'usage' => [
                'adult' => 1,
            ],
            'offers' => [
                "price_row_collection_per_meal_plan"=> [
                    2 => [
                        "price_rows"=> [
                            "Single"=> [
                                "price"=> ["net"=> 600.0, "margin"=> 60.0, "rack"=> 660.0], 
                                "meta"=> [
                                    "age_range"=> "adult", 
                                    "mandatory"=> true, 
                                    "product_id"=> 1, 
                                    "extra"=> false, 
                                    "product_type_taxonomy_id"=> 60, 
                                    "price_name"=> "Single", 
                                    "amount"=> 1, 
                                    "non_empty_date_ranges"=> [1, 2, 3, 5, 6, 8], 
                                    "id"=> 1, 
                                    "productable_id"=> 1
                                ], 
                                "amount"=> 1
                            ]
                        ]
                    ], 
                    3 => [
                        "price_rows"=> [
                            "Single"=> [
                                "price"=> ["net"=> 606.0, "margin"=> 60.59999999999991, "rack"=> 666.5999999999999], 
                                "meta"=> [
                                    "age_range"=> "adult", 
                                    "mandatory"=> true, 
                                    "product_id"=> 1, 
                                    "extra"=> false, 
                                    "product_type_taxonomy_id"=> 60, 
                                    "price_name"=> "Single", 
                                    "amount"=> 1, 
                                    "non_empty_date_ranges"=> [1, 2, 3, 5, 6, 8], 
                                    "id"=> 1, 
                                    "productable_id"=> 1
                                ], 
                                "amount"=> 1
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_calculate_with_extra_prices()
    {
        $actual = $this->jsonDecode(
            $this->prepareAndRun("print json.dumps(price_calculator.get_offers(factory.room_request(usage_elements=[
                { 'age': 21, 'amount': 1 },
                { 'age': 6, 'amount': 1 }
            ])))"), true
        );
        $expected = [
            'room_request' => [
                'usage' => [
                    [
                        'age' => 21,
                        'amount' => 1,
                    ],
                    [
                        'age' => 6,
                        'amount' => 1,
                    ]
                ],
            ],
            'usage' => [
                'adult' => 1,
                'child' => 1
            ],
            'offers' => array(
                'price_row_collection_per_meal_plan' => [
                    2 => [
                        'price_rows' => [
                            'Single' => [
                                'price' => [
                                    'net' => 600.0,
                                    'rack' => 660.0,
                                    'margin' => 60.0
                                ],
                                'meta' => [
                                    'age_range' => 'adult',
                                    'mandatory' => true,
                                    'product_id' => 1,
                                    'extra' => false,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => 'Single',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [1,2,3,5,6,8],
                                    'id' => 1,
                                    'productable_id' => 1,
                                ],
                                'amount' => 1
                            ],
                            'Extra Child' => [
                                'price' => [
                                    'net' => 300,
                                    'rack' => 330,
                                    'margin' => 30
                                ],
                                'meta' => [
                                    'age_range' => 'child',
                                    'mandatory' => false,
                                    'product_id' => 1,
                                    'extra' => true,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => 'Extra Child',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [1,2,3,5,6,8],
                                    'id' => 2,
                                    'productable_id' => 1,
                                ],
                                'amount' => 1
                            ]
                        ]
                    ],
                    3 => [
                        'price_rows' => [
                            'Single' => [
                                'price' => [
                                    'net' => 606.0,
                                    'rack' => 666.59999999999991,
                                    'margin' => 60.599999999999909
                                ],
                                'meta' => [
                                    'age_range' => 'adult',
                                    'mandatory' => true,
                                    'product_id' => 1,
                                    'extra' => false,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => 'Single',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [1,2,3,5,6,8],
                                    'id' => 1,
                                    'productable_id' => 1,
                                ],
                                'amount' => 1
                            ],
                            'Extra Child' => [
                                'price' => [
                                    'net' => 306.0,
                                    'rack' => 336.60000000000002,
                                    'margin' => 30.600000000000023
                                ],
                                'meta' => [
                                    'age_range' => 'child',
                                    'mandatory' => false,
                                    'product_id' => 1,
                                    'extra' => true,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => 'Extra Child',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [1,2,3,5,6,8],
                                    'id' => 2,
                                    'productable_id' => 1,
                                ],
                                'amount' => 1
                            ]
                        ]
                    ]
                ]
            )
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_calculate_with_two_extra_prices()
    {
        $actual = $this->jsonDecode(
            $this->prepareAndRun("print json.dumps(price_calculator.get_offers(factory.room_request(usage_elements=[
                { 'age': 21, 'amount': 1 },
                { 'age': 6, 'amount': 1 },
                { 'age': 5, 'amount': 1 }
            ])))"), true
        );
        $expected = [
            'room_request' => [
                'usage' => [
                    [
                        'age' => 21,
                        'amount' => 1,
                    ],
                    [
                        'age' => 6,
                        'amount' => 1,
                    ],
                    [
                        'age' => 5,
                        'amount' => 1,
                    ]
                ],
            ],
            'usage' => [
                'adult' => 1,
                'child' => 2
            ],
            'offers' => [
                'price_row_collection_per_meal_plan' => [
                    2 => [
                        'price_rows' => [
                            'Single' => [
                                'price' => [
                                    'net' => 600.0,
                                    'rack' => 660.0,
                                    'margin' => 60.0
                                ],
                                'meta' => [
                                    'age_range' => 'adult',
                                    'mandatory' => true,
                                    'product_id' => 1,
                                    'extra' => false,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => 'Single',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [1,2,3,5,6,8],
                                    'id' => 1,
                                    'productable_id' => 1,
                                ],
                                'amount' => 1
                            ],
                            'Extra Child' => [
                                'price' => [
                                    'net' => 300,
                                    'rack' => 330,
                                    'margin' => 30
                                ],
                                'meta' => [
                                    'age_range' => 'child',
                                    'mandatory' => false,
                                    'product_id' => 1,
                                    'extra' => true,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => 'Extra Child',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [1,2,3,5,6,8],
                                    'id' => 2,
                                    'productable_id' => 1,
                                ],
                                'amount' => 2
                            ]
                        ]
                    ],
                    3 => [
                        'price_rows' => [
                            'Single' => [
                                'price' => [
                                    'net' => 606.0,
                                    'rack' => 666.59999999999991,
                                    'margin' => 60.599999999999909
                                ],
                                'meta' => [
                                    'age_range' => 'adult',
                                    'mandatory' => true,
                                    'product_id' => 1,
                                    'extra' => false,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => 'Single',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [1,2,3,5,6,8],
                                    'id' => 1,
                                    'productable_id' => 1,
                                ],
                                'amount' => 1
                            ],
                            'Extra Child' => [
                                'price' => [
                                    'net' => 306.0,
                                    'rack' => 336.60000000000002,
                                    'margin' => 30.600000000000023
                                ],
                                'meta' => [
                                    'age_range' => 'child',
                                    'mandatory' => false,
                                    'product_id' => 1,
                                    'extra' => true,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => 'Extra Child',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [1,2,3,5,6,8],
                                    'id' => 2,
                                    'productable_id' => 1,
                                ],
                                'amount' => 2
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_find_price_for_a_child_on_an_adult_bed()
    {
        $this->markTestSkipped("Modified, extra child can't get extra adult price");
        $actual = $this->jsonDecode(
            $this->prepareAndRun("print price_calculator.get_offers(factory.room_request(usage_elements=[
                { 'age': 21, 'amount': 1 },
                { 'age': 1, 'amount': 1 }
            ]))", 1, 1, '2026-09-02','2026-09-08'), true
        );
        $expected = [
            'room_request' => [
                'usage' => [
                    [
                        'age' => 21,
                        'amount' => 1,
                    ],
                    [
                        'age' => 1,
                        'amount' => 1,
                    ]
                ],
            ],
            'usage' => [
                'adult' => 1,
                'baby' => 1
            ],
            'offers' => [
                2 => [
                    'net' => 960,
                    'rack' => 1056,
                    'margin' => 96
                ],
                3 => [
                    'net' => 960,
                    'rack' => 1056,
                    'margin' => 96
                ]
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_find_zero_price_for_a_free_age_range()
    {
        $actual = $this->jsonDecode(
            $this->prepareAndRun("print json.dumps(price_calculator.get_offers(factory.room_request(usage_elements=[
                { 'age': 21, 'amount': 1 },
                { 'age': 16, 'amount': 1 }
            ])))", 15, 1, '2027-06-02', '2027-06-08'), true
        );
        $expected = [
            'room_request' => [
                'usage' => [
                    [
                        'age' => 21,
                        'amount' => 1,
                    ],
                    [
                        'age' => 16,
                        'amount' => 1,
                    ]
                ],
            ],
            'usage' => [
                'adult' => 1
            ],
            'offers' => [
                'price_row_collection_per_meal_plan' => [
                    2 => [
                        'price_rows' => [
                            '1 Pax' => [
                                'price' => [
                                    'net' => 417.391304347824,
                                    'rack' => 480.0,
                                    'margin' => 62.608695652175982
                                ],
                                'meta' => [
                                    'age_range' => 'adult',
                                    'mandatory' => true,
                                    'product_id' => 26,
                                    'extra' => false,
                                    'product_type_taxonomy_id' => 60,
                                    'price_name' => '1 Pax',
                                    'amount' => 1,
                                    'non_empty_date_ranges' => [
                                        0 => 97,
                                        1 => 98,
                                        2 => 99
                                    ],
                                    'id' => 55,
                                    'productable_id' => 24,
                                ],
                                'amount' => 1
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    const H16_DOUBLE_PRICE = 200;
    const H16_MANDATORY_PRICE = self::H16_DOUBLE_PRICE;
    const H16_SINGLE_PRICE = 110;
    const H16_EXTRA_ADULT_PRICE = 180;
    const H16_EXTRA_CHILD_PRICE = 80;
    const H16_MARGIN_MODIFIER = 1.15;

    private function h16find($usage_elements, $offers, $deduction = false, $deductionBasePrices=null)
    {
        /* Default usage for this room: {"adult":3,"child":3}, Mandatory price: {adult: 2} */
        $usage = [];
        foreach ($usage_elements as $ue) {
            $usage[$ue['age'] == 21 ? 'adult' : 'child'] = $ue['amount'];
        }
        $actual = $this->jsonDecode(
            $this->prepareAndRun(
                "print json.dumps(price_calculator.get_offers(factory.room_request(usage_elements=" . \json_encode($usage_elements) . ")))",
                16,
                0,
                '2026-06-02',
                '2026-06-03',
                $deduction ? 'True' : 'False',
                $deductionBasePrices?:'None'
            ),
            true
        );
        $expected = [
            'room_request' => ['usage' => $usage_elements],
            'usage' => $usage,
            'offers' => $offers
        ];
        return [$expected, $actual];
    }

    static private function getNetRack($rack)
    {
        return [
            'net' => $rack / self::H16_MARGIN_MODIFIER,
            'rack' => $rack,
            'margin' => $rack - $rack / self::H16_MARGIN_MODIFIER
        ];
    }

    /**
     * @test
     */
    public function it_can_compare_price_for_normal_price_logic_and_deduction_base_usage_for_3a_and_1c()
    {
        $usage_elements = [
            ['age' => 21, 'amount' => 3],
            ['age' => 4, 'amount' => 1]
        ];
        $offersNormal = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Double Adult' => [
                            'price' => self::getNetRack(self::H16_DOUBLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => true,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Double Adult',
                                'amount' => 2,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 58,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Adult' => [
                            'price' => self::getNetRack(self::H16_EXTRA_ADULT_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Adult',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 59,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ]
                    ]
                ]
            ]
        ];
        $offersDeduction = $offersNormal;
        $deductionBasePrices ='{"Extra Child":1,"Extra Adult":1,"Double Adult":1}';

        list($expectedNormal, $actualNormal) = $this->h16find($usage_elements, $offersNormal, false);
        $this->assertEquals($expectedNormal, $actualNormal);

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

    /**
     * @test
     */
    public function it_can_compare_price_for_normal_price_logic_and_deduction_base_usage_for_1a()
    {
        $usage_elements = [
            ['age' => 21, 'amount' => 1]
        ];
        $offersNormal = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Single Adult' => [
                            'price' => self::getNetRack(self::H16_SINGLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Single Adult',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 57,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ]
                    ]
                ]
            ]
        ];
        $offersDeduction = $offersNormal;
        $deductionBasePrices ='{"Single Adult":1}';

        list($expectedNormal, $actualNormal) = $this->h16find($usage_elements, $offersNormal, false);
        $this->assertEquals($expectedNormal, $actualNormal);

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);

    }

    /**
     * @test
     */
    public function it_can_compare_price_for_normal_price_logic_and_deduction_base_usage_for_1a_and_1c()
    {
        $usage_elements = [
            ['age' => 21, 'amount' => 1],
            ['age' => 4, 'amount' => 1]
        ];
        $offersNormal = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Double Adult' => [
                            'price' => self::getNetRack(self::H16_DOUBLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => true,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Double Adult',
                                'amount' => 2,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 58,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ]
                    ]
                ]
            ]
        ];
        $offersDeduction = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Single Adult' => [
                            'price' => self::getNetRack(self::H16_SINGLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Single Adult',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 57,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices ='{"Extra Child":1,"Single Adult":1}';

        list($expectedNormal, $actualNormal) = $this->h16find($usage_elements, $offersNormal, false);
        $this->assertEquals($expectedNormal, $actualNormal);

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

    /**
     * @test
     */
    public function it_can_compare_price_for_normal_price_logic_and_deduction_base_usage_for_1a_and_3c()
    {
        $usage_elements = [
            ['age' => 21, 'amount' => 1],
            ['age' => 4, 'amount' => 3]
        ];
        $offersNormal = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Double Adult' => [
                            'price' => self::getNetRack(self::H16_DOUBLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => true,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Double Adult',
                                'amount' => 2,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 58,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 2
                        ]
                    ]
                ]
            ]
        ];
        $offersDeduction = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Single Adult' => [
                            'price' => self::getNetRack(self::H16_SINGLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Single Adult',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 57,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 3
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices ='{"Extra Child":3,"Single Adult":1}';

        list($expectedNormal, $actualNormal) = $this->h16find($usage_elements, $offersNormal, false);
        $this->assertEquals($expectedNormal, $actualNormal);

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

    /**
     * @test
     */
    public function it_can_compare_price_for_normal_price_logic_and_deduction_base_usage_for_1a_and_4c()
    {
        $usage_elements = [
            ['age' => 21, 'amount' => 1],
            ['age' => 4, 'amount' => 4]
        ];
        $offersNormal = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Double Adult' => [
                            'price' => self::getNetRack(self::H16_DOUBLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => true,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Double Adult',
                                'amount' => 2,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 58,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 3
                        ]
                    ]
                ]
            ]
        ];
        $offersDeduction = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Single Adult' => [
                            'price' => self::getNetRack(self::H16_SINGLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Single Adult',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 57,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 4
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices ='{"Extra Child":4,"Single Adult":1}';

        list($expectedNormal, $actualNormal) = $this->h16find($usage_elements, $offersNormal, false);
        $this->assertEquals($expectedNormal, $actualNormal);

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

    /**
     * @test
     */
    public function it_can_compare_price_for_normal_price_logic_and_deduction_base_usage_for_2a_and_4c()
    {
        $usage_elements = [
            ['age' => 21, 'amount' => 2],
            ['age' => 4, 'amount' => 4]
        ];
        $offersNormal = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Double Adult' => [
                            'price' => self::getNetRack(self::H16_DOUBLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => true,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Double Adult',
                                'amount' => 2,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 58,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 4
                        ]
                    ]
                ]
            ]
        ];
        $offersDeduction = $offersNormal;
        $deductionBasePrices ='{"Extra Child":4,"Double Adult":1}';

        list($expectedNormal, $actualNormal) = $this->h16find($usage_elements, $offersNormal, false);
        $this->assertEquals($expectedNormal, $actualNormal);

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

    /**
     * @test
     */
    public function it_can_compare_price_for_normal_price_logic_and_deduction_base_usage_for_0a_and_3c()
    {
        $usage_elements = [
            ['age' => 4, 'amount' => 3]
        ];
        $offersNormal = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Double Adult' => [
                            'price' => self::getNetRack(self::H16_DOUBLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => true,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Double Adult',
                                'amount' => 2,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 58,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ]
                    ]
                ]
            ]
        ];
        $offersDeduction = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 3
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices ='{"Extra Child":3}';

        list($expectedNormal, $actualNormal) = $this->h16find($usage_elements, $offersNormal, false);
        $this->assertEquals($expectedNormal, $actualNormal);

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

    /**
     * @test
     */
    public function it_can_compare_price_for_normal_price_logic_and_deduction_base_usage_for_0a_and_5c()
    {
        $usage_elements = [
            ['age' => 4, 'amount' => 5]
        ];
        $offersNormal = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Double Adult' => [
                            'price' => self::getNetRack(self::H16_DOUBLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => true,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Double Adult',
                                'amount' => 2,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 58,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ],
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 3
                        ]
                    ]
                ]
            ]
        ];
        $offersDeduction = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 5
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices ='{"Extra Child":5}';

        list($expectedNormal, $actualNormal) = $this->h16find($usage_elements, $offersNormal, false);
        $this->assertEquals($expectedNormal, $actualNormal);

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

    /**
     * @test
     */
    public function it_can_calculate_price_when_deduction_base_is_smaller_for_0a_and_3c_()
    {
        $usage_elements = [
            ['age' => 4, 'amount' => 3]
        ];
        $offersDeduction = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 2
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices ='{"Extra Child":2}';

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

    /**
     * @test
     */
    public function it_can_calculate_price_when_deduction_base_is_smaller_for_0a_and_2c_using_base_prices()
    {
        $usage_elements = [
            ['age' => 4, 'amount' => 2]
        ];

        $offersDeduction = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Double Adult' => [
                            'price' => self::getNetRack(self::H16_DOUBLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => true,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Double Adult',
                                'amount' => 2,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 58,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices ='{"Double Adult":1}';

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, false, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);

        $offersDeduction2 = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Extra Child' => [
                            'price' => self::getNetRack(self::H16_EXTRA_CHILD_PRICE),
                            'meta' => [
                                'age_range' => 'child',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => true,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Extra Child',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                ],
                                'id' => 60,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices2 ='{"Single Adult":1, "Extra Child":1}';

        list($expectedDeduction2, $actualDeduction2) = $this->h16find($usage_elements, $offersDeduction2, true, $deductionBasePrices2);
        $this->assertEquals($expectedDeduction2, $actualDeduction2);
    }

    /**
     * @test
     */
    public function it_can_calculate_price_when_deduction_base_is_smaller_for_2a_and_0c_using_base_prices()
    {
        $usage_elements = [
            ['age' => 21, 'amount' => 2]
        ];

        $offersDeduction = [
            'price_row_collection_per_meal_plan' => [
                2 => [
                    'price_rows' => [
                        'Single Adult' => [
                            'price' => self::getNetRack(self::H16_SINGLE_PRICE),
                            'meta' => [
                                'age_range' => 'adult',
                                'mandatory' => false,
                                'product_id' => 27,
                                'extra' => false,
                                'product_type_taxonomy_id' => 60,
                                'price_name' => 'Single Adult',
                                'amount' => 1,
                                'non_empty_date_ranges' => [
                                    0 => 103,
                                    1 => 104,
                                    2 => 105
                                ],
                                'id' => 57,
                                'productable_id' => 25,
                            ],
                            'amount' => 1
                        ]
                    ]
                ]
            ]
        ];
        $deductionBasePrices ='{"Single Adult":1}';

        list($expectedDeduction, $actualDeduction) = $this->h16find($usage_elements, $offersDeduction, true, $deductionBasePrices);
        $this->assertEquals($expectedDeduction, $actualDeduction);
    }

}
