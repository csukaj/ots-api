<?php

namespace Tests\Procedure\Ots\Pricing;

use Tests\Procedure\ProcedureTestCase;

class PriceCalculatorTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.pricing.price_calculator import PriceCalculator' . PHP_EOL;

    private function prepareAndRun($organizationId, $script)
    {
        $config = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => '2026-06-02 12:00:00',
            'to_time' => '2026-06-08 12:00:00',
            'productable_type' => 'App\\\\Device',
            'productable_id' => $this->scriptContainer("factory.device('App\\Organization', {$organizationId})['id']"),
            'price_modifiable_type' => 'App\\\\Organization',
            'price_modifiable_id' => $organizationId,
            'date_ranges' => $this->scriptContainer("{62:factory.open_date_ranges({$organizationId})}"),
            'age_resolver' => $this->scriptContainer("factory.age_resolver('App\\\\Organization', {$organizationId})")
        ]);
        return $this->runPythonScript(self::$imports . "price_calculator = PriceCalculator({$config})" . PHP_EOL . $script);
    }

    private function assertPriceAmounts($expected, $actual)
    {
        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertCount(count($expected[$i]), $actual[$i]);
            $this->assertEquals([], array_diff_assoc($expected[$i]['price'], $actual[$i]['price']));
            $this->assertEquals($expected[$i]['amount'], $actual[$i]['amount']);
            $this->assertEquals($expected[$i]['net_offer'], $actual[$i]['net_offer']);
            $this->assertEquals($expected[$i]['rack_offer'], $actual[$i]['rack_offer']);
        }
    }

    /**
     * @test
     */
    public function it_can_init_itself()
    {
        $actual = $this->prepareAndRun(1, '');
        $this->assertEquals('', $actual);
    }

    /**
     * @test
     */
    public function it_can_get_offers()
    {
        $actual = $this->jsonDecode(
            $this->prepareAndRun(1,
            '
import json
print json.dumps(price_calculator.get_offers(factory.room_request()))
            '), 
            true
        );

        $expected = [
            'usage' => [
                'adult' => 1
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
            ],
            'room_request' => [
                'usage' => [
                    [
                        'age' => 21,
                        'amount' => 1,
                    ]
                ]
            ]
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_get_device_price_amounts_for_a_single_room()
    {
        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 1}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;

        $actual = $this->jsonDecode($this->prepareAndRun(1, $script), true);
        $expected = [
            [
                'price' => [
                    'amount' => 1,
                    'age_range' => 'adult',
                    'mandatory' => true,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
                price_calculator.prices,
                {"adult": 2}, 
                price_calculator.date_ranges[0], 
                price_calculator.deduction_base_prices,
                price_calculator.product_ids
            ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(1, $script), true);
        $this->assertNull($actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
                price_calculator.prices,
                {"adult": 1, "child": 2}, 
                price_calculator.date_ranges[0], 
                price_calculator.deduction_base_prices,
                price_calculator.product_ids
            ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(1, $script), true);
        $expected = [
            [
                'price' => [
                    'amount' => 1,
                    'age_range' => 'adult',
                    'mandatory' => true,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ],
            [
                'price' => [
                    'amount' => 1,
                    'age_range' => 'child',
                    'mandatory' => false,
                    'extra' => true
                ],
                'amount' => 2,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_get_device_price_amounts_for_a_double_room()
    {
        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices,
            {"adult": 1},
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(2, $script), true);
        $expected = [
            [
                'price' => [
                    'amount' => 2,
                    'age_range' => 'adult',
                    'mandatory' => true,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 2}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(2, $script), true);
        $expected = [
            [
                'price' => [
                    'amount' => 2,
                    'age_range' => 'adult',
                    'mandatory' => true,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 1, "child": 2}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(2, $script), true);
        $expected = [
            [
                'price' => [
                    'amount' => 2,
                    'age_range' => 'adult',
                    'mandatory' => true,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ],
            [
                'price' => [
                    'amount' => 1,
                    'age_range' => 'child',
                    'mandatory' => false,
                    'extra' => true
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 3}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(2, $script), true);
        $this->assertNull($actual);
    }

    /**
     * @test
     */
    public function it_can_get_device_price_amounts_for_a_comfy_room()
    {
        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 1}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);

        $expected = [
            [
                'price' => [
                    'amount' => 1,
                    'age_range' => 'adult',
                    'mandatory' => false,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 2}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $expected = [
            [
                'price' => [
                    'amount' => 2,
                    'age_range' => 'adult',
                    'mandatory' => true,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 1, "child": 2}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $expected = [
            [
                'price' => [
                    'amount' => 2,
                    'age_range' => 'adult',
                    'mandatory' => true,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ],
            [
                'price' => [
                    'amount' => 1,
                    'age_range' => 'child',
                    'mandatory' => false,
                    'extra' => true
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 3}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $expected = [
            [
                'price' => [
                    'amount' => 2,
                    'age_range' => 'adult',
                    'mandatory' => true,
                    'extra' => false
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ],
            [
                'price' => [
                    'amount' => 1,
                    'age_range' => 'adult',
                    'mandatory' => false,
                    'extra' => true
                ],
                'amount' => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print dumps(price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            {"adult": 1, "baby": 2}, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        ))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $this->assertEmpty($actual);
    }

    /**
     * @test
     */
    public function it_can_find_price_for_extras()
    {
        $script = 'print price_calculator._find_price_for_extras(price_calculator.prices, "adult", {"adult": 1, "child": 0, "baby": 0}, [], price_calculator.date_ranges[0])' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $expected = [
            [
                "price" => [
                    "amount" => 1,
                    "age_range" => "adult",
                    "mandatory" => false,
                    "extra" => true
                ],
                "amount" => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print price_calculator._find_price_for_extras(price_calculator.prices, "adult", {"adult": 2, "child": 0, "baby": 0}, [], price_calculator.date_ranges[0])' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $expected = [
            [
                "price" => [
                    "amount" => 1,
                    "age_range" => "adult",
                    "mandatory" => false,
                    "extra" => true
                ],
                "amount" => 2,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print price_calculator._find_price_for_extras(price_calculator.prices, "child", {"adult": 0, "child": 1, "baby": 0}, [], price_calculator.date_ranges[0])' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $expected = [
            [
                "price" => [
                    "amount" => 1,
                    "age_range" => "child",
                    "mandatory" => false,
                    "extra" => true
                ],
                "amount" => 1,
                'net_offer' => [],
                'rack_offer' => []
            ]
        ];
        $this->assertPriceAmounts($expected, $actual);

        $script = 'print price_calculator._find_price_for_extras(price_calculator.prices, "baby", {"adult": 0, "child": 0, "baby": 1}, [], price_calculator.date_ranges[0])' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $this->assertEmpty($actual);

        $script = 'print price_calculator._find_price_for_extras(price_calculator.prices, "child", {"adult": 1, "child": 0, "baby": 0}, [], price_calculator.date_ranges[0])' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(16, $script), true);
        $expected = [];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_get_base_prices()
    {
        $script = 'loaded_prices = price_calculator.prices[60]' . PHP_EOL;
        $script .= 'resultset = pickle_resultset(loaded_prices)' . PHP_EOL;
        $script .= 'base_prices = price_calculator._get_base_prices(price_calculator.prices,price_calculator.date_ranges[0])' . PHP_EOL;
        $script .= 'print dumps([resultset,base_prices])' . PHP_EOL;
        list($prices, $actual) = $this->jsonDecode($this->prepareAndRun(1, $script), true);
        $expected = [];
        foreach ($prices as $price) {
            if (!$price["extra"]) {
                $expected[] = $price;
            }
        }
        $sorter = function ($a, $b) {
            return ($a['id'] < $b['id']) ? -1 : 1;
        };
        usort($expected, $sorter);
        usort($actual, $sorter);
        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals($expected[$i]['id'], $actual[$i]['id']);
        }
    }

    /**
     * @test
     */
    public function it_can_get_extra_prices()
    {
        $script = 'loaded_prices = price_calculator.prices[60]' . PHP_EOL;
        $script .= 'resultset = pickle_resultset(loaded_prices)' . PHP_EOL;
        $script .= 'extra_prices = price_calculator._get_extra_prices(price_calculator.prices, price_calculator.date_ranges[0])' . PHP_EOL;
        $script .= 'print dumps([resultset,extra_prices])' . PHP_EOL;
        list($prices, $actual) = $this->jsonDecode($this->prepareAndRun(1, $script), true);
        $expected = [];
        foreach ($prices as $price) {
            if ($price["extra"]) {
                $expected[] = $price;
            }
        }
        $sorter = function ($a, $b) {
            return ($a['id'] < $b['id']) ? -1 : 1;
        };
        usort($expected, $sorter);
        usort($actual, $sorter);
        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals($expected[$i]['id'], $actual[$i]['id']);
        }
    }

    /**
     * @test
     */
    public function it_can_get_mandatory_price()
    {
        $script = 'loaded_prices = price_calculator.prices[60]' . PHP_EOL;
        $script .= 'resultset = pickle_resultset(loaded_prices)' . PHP_EOL;
        $script .= 'mandatory_price = price_calculator._get_mandatory_price(price_calculator.prices, price_calculator.date_ranges[0])' . PHP_EOL;
        $script .= 'print dumps([resultset,mandatory_price])' . PHP_EOL;
        list($prices, $actual) = $this->jsonDecode($this->prepareAndRun(1, $script), true);
        $expected = [];
        foreach ($prices as $price) {
            if ($price["mandatory"]) {
                $expected[] = $price;
            }
        }

        $this->assertEquals(1, count($expected));
        $this->assertEquals($expected[0]['id'], $actual['id']);
        $this->assertTrue($actual['mandatory']);
    }

    /**
     * @test
     */
    public function it_can_load_prices()
    {

        $script = 'loaded = price_calculator._get_prices(
            True, 
            price_calculator.productable_type, 
            price_calculator.productable_id, 
            price_calculator.product_ids
        )' . PHP_EOL;
        $script .= 'loaded_prices = loaded[60]' . PHP_EOL;
        $script .= 'resultset = pickle_resultset(loaded_prices)' . PHP_EOL;
        $script .= 'print dumps(resultset)' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(1, $script), true);

        $this->assertCount(3, $actual);
        foreach ($actual as $row) {
            $this->assertEquals([], array_diff(["id", "age_range", "amount", "extra", "mandatory"], array_keys($row)));
        }
    }

    /**
     * @test
     */
    public function it_can_get_date_range_ids()
    {
        $script = 'print dumps([pickle_resultset(price_calculator.date_ranges),price_calculator._get_date_range_ids(price_calculator.date_ranges)])' . PHP_EOL;
        list($ranges, $actual) = $this->jsonDecode($this->prepareAndRun(1, $script), true);
        $expected = [];
        foreach ($ranges as $range) {
            if ($range["id"]) {
                $expected[] = $range['id'];
            }
        }
        sort($expected);
        sort($actual);
        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_calculate_price_offers()
    {
        $script = "usage = price_calculator.age_resolver.resolve_room_usage([{ 'age': 21, 'amount': 1 }], True)" . PHP_EOL;
        $script .= 'price_amounts = price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            usage, 
            price_calculator.date_ranges[-1], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        )' . PHP_EOL;
        $script .= 'print dumps(price_calculator._calculate_price_offers(price_amounts, price_calculator.date_ranges[-1], price_calculator.applied_date_ranges, price_calculator.price_elements, price_calculator.common_meal_plans, price_calculator.uncovered_nights))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(2, $script), true);
        $expected = [
            [
                'price' =>
                    [
                        'amount' => 2,
                        'age_range' => 'adult',
                        'mandatory' => true,
                        'id' => 15,
                        'extra' => false,
                        'price_name' => 'Some price name',
                        'non_empty_date_ranges' => [1],
                        'product_id' => 1,
                        'product_type_taxonomy_id' => 60,
                        'productable_id' => 1
                    ],
                'amount' => 1,
                'net_offer' => [3 => 660, 2 => 660],
                'rack_offer' => [3 => 792, 2 => 792]
            ]
        ];
        $this->assertEquals(count($expected), count($actual));

        for ($i = 0; $i < count($expected); $i++) {
            $expectedPriceKeys = array_keys($expected[$i]['price']);
            $actualPriceKeys = array_keys($actual[$i]['price']);
            sort($expectedPriceKeys);
            sort($actualPriceKeys);
            $this->assertEquals($expectedPriceKeys, $actualPriceKeys);
            $this->assertEquals($expected[$i]['amount'], $actual[$i]['amount']);
            $this->assertEquals($expected[$i]['net_offer'], $actual[$i]['net_offer']);
            $this->assertEquals($expected[$i]['rack_offer'], $actual[$i]['rack_offer']);
        }
    }

    /**
     * @test
     */
    public function it_can_filter_price_elements_for_date_range_and_price_ids()
    {
        $script = 'usage = price_calculator.age_resolver.resolve_room_usage(factory.room_usage())' . PHP_EOL;
        $script .= 'price_amounts = price_calculator._get_device_price_amounts(
            price_calculator.prices, 
            usage, 
            price_calculator.date_ranges[0], 
            price_calculator.deduction_base_prices,
            price_calculator.product_ids
        )' . PHP_EOL;
        $script .= 'price_elements = price_calculator._filter_price_elements_for_date_range_and_price_ids(
            [
                price_amount["price"]["id"] for price_amount in price_amounts
                if price_amount["price"] and "id" in price_amount["price"]
            ],
            price_calculator.date_ranges[0]["id"],
            price_elements=price_calculator.price_elements,
            common_meal_plans=price_calculator.common_meal_plans
        )' . PHP_EOL;
        $script .= 'print dumps(pickle_resultset(price_elements))' . PHP_EOL;
        $actual = $this->jsonDecode($this->prepareAndRun(1, $script), true);
        $expected = [
            'margin_type_taxonomy_id' => 57,
            'meal_plan_id' => 2,
            'rack_price' => 110.0,
            'created_at' => '2017-01-23 15:10:14',
            'net_price' => 100.0,
            'updated_at' => '2017-01-23 15:10:14',
            'date_range_id' => 575,
            'price_id' => 344,
            'model_meal_plan_id' => 715,
            'deleted_at' => null,
            'margin_value' => 10.0,
            'id' => 2514
        ];
        ksort($expected);
        $this->assertCount(2, $actual);
        ksort($actual[0]);
        ksort($actual[1]);
        $this->assertEquals(array_keys($expected), array_keys($actual[0]));
        $this->assertEquals(array_keys($expected), array_keys($actual[1]));
    }


}
