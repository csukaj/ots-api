<?php

namespace Tests\Procedure\Ots\Search;

use App\Cruise;
use App\PriceModifier;
use Tests\Procedure\ProcedureTestCase;

class CruiseSearchTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.cruise_search import CruiseSearch' . PHP_EOL .
    'from ots.common.config import Config' . PHP_EOL .
    'from json import dumps, loads' . PHP_EOL .
    'import copy' . PHP_EOL;

    const DATE_RANGE_TYPE_OPEN = 62;
    const DATE_RANGE_TYPE_CLOSED = 63;
    const DATE_RANGE_TYPE_PRICE_MODIFIER = 164;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function prepareCruiseSearch(
        $organizationId = 1,
        $cruiseId = 1,
        $request = [],
        $fromDate = null,
        $toDate = null,
        $bookingDate = null,
        $weddingDate = null
    ) {
        $cruiseSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $organizationId,
            'cruise_id' => $cruiseId,
            'params' => json_encode([
                'request' => $request,
                'interval' => [
                    'date_from' => $fromDate,
                    'date_to' => $toDate,
                ],
                'booking_date' => $bookingDate,
                'wedding_date' => $weddingDate
            ])
        ]);
        return self::$imports . PHP_EOL . "cruise_search = CruiseSearch({$cruiseSearchConfig})" . PHP_EOL;
    }

    /**
     * @test
     */
    public function it_can_init_itself()
    {
        $result = $this->runPythonScript($this->prepareCruiseSearch());
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function it_works_when_there_are_no_available_cabins()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2010-06-02', '2010-06-08');
        $script = $cruiseSearch . "print cruise_search.get_cabins()" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertEquals('[]', $actual);
    }

    /**
     * @test
     */
    public function it_works_when_there_are_available_cabins_but_no_dates_specified()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'));
        $script = $cruiseSearch . "print cruise_search.get_cabins()" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script, true, true);
        $this->assertGreaterThan(0, $this->count($actual));
        foreach ($actual as $row) {
            $this->assertArrayHasKey('availability', $row);
            $this->assertCount(2, $row['availability']);
            $this->assertArrayHasKey('results', $row);
            $this->assertNull($row['results']);
        }
    }

    /**
     * @test
     */
    public function it_works_when_there_are_no_results()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1, 1, json_decode('[{"usage":[{ "age": 21, "amount": 15 }]}]'),
            '2026-06-01', '2026-07-05');
        $script = $cruiseSearch . "print cruise_search.get_cabins()" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertEquals('[]', $actual);
    }

    /**
     * @test
     */
    public function it_works_when_there_are_good_results()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2026-06-01', '2026-07-05');
        $script = $cruiseSearch . "print cruise_search.get_cabins()" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);

        $this->assertGreaterThan(0, $this->count($actual));
        foreach ($actual as $row) {
            $this->assertArrayHasKey('schedule', $row);
            $this->assertArrayHasKey('availability', $row);
            $this->assertCount(2, $row['availability']);
            $this->assertArrayHasKey('results', $row);
            $this->assertCount(2, $row['results'][0]);
        }
    }

    /**
     * @test
     */
    public function it_can_calculate_device_prices()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2026-06-02', '2026-06-08');
        $script = $cruiseSearch . "cruise_search.get_cabins()" . PHP_EOL;
        $script .= "print dumps(cruise_search._calculate_device_prices(0, cruise_search.cabin_matcher.check()[0]['device_id']))" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);

        $this->assertArrayHasKey('device_id', $actual);
        $this->assertArrayHasKey('prices', $actual);
        $this->assertCount(2, $actual['prices']);
        $this->assertArrayHasKey('usages', $actual);
        $this->assertEquals(['adult' => 1], $actual['usages']);
    }

    /**
     * @test
     */
    public function it_can_get_best_device_prices()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2026-06-02', '2026-06-08');
        $script = $cruiseSearch . "cruise_search.get_cabins()" . PHP_EOL;
        $script .= "device = factory.device('App\\ShipGroup', 1)" . PHP_EOL;
        $script .= "usage_price = cruise_search.price_search.find(
                0,
                cruise_search.request_handler.request[0],
                device['id'],
                None,
                cruise_search.date_ranges[Config.DATE_RANGE_TYPE_OPEN]
            )" . PHP_EOL;
        $script .= "print dumps(cruise_search._best_productable_prices([usage_price], 'device_id', device['id']))" . PHP_EOL;

        $actual = $this->runPythonAndDecodeJSON($script);
        $actual_without_unneeded = array_map(function($dict) {
            unset($dict['switches']);
            unset($dict['meal_offer']);
            return $dict;
        }, $actual['prices']);
        $expected = [
            [
                'original_price' => 330.0,
                'order_itemable_index' => 0,
                'discounted_price' => 330.0,
                'meal_plan' => 'e/p',
                'discounts' => [],
                'meal_plan_id' => 1,
                'period' => ['date_from' => null, 'date_to' => null],
                'room_request' => [
                    'usage' => [
                        [
                            'age' => 21,
                            'amount' => 1
                        ]
                    ]
                ],
                'has_merged_free_nights' => false,
                'margin' => 43.043478260870984
            ],
            [
                'original_price' => 600.0,
                'order_itemable_index' => 0,
                'discounted_price' => 600.0,
                'meal_plan' => 'f/b',
                'discounts' => [],
                'meal_plan_id' => 4,
                'period' => ['date_from' => null, 'date_to' => null],
                'room_request' => [
                    'usage' => [
                        [
                            'age' => 21,
                            'amount' => 1
                        ]
                    ]
                ],
                'has_merged_free_nights' => false,
                'margin' => 78.260869565219991
            ]

        ];
        $this->assertEquals($expected, $actual_without_unneeded);
    }

    /**
     * @test
     */
    public function it_can_load_request_handler()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1, 1, $this->scriptContainer('factory.request()'), '2026-06-02',
            '2026-06-08');
        $script = $cruiseSearch . "cruise_search._load_request_handler('App\\Organization', 1)" . PHP_EOL;
        $script .= "print cruise_search.request_handler" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertTrue(strpos($actual, '<ots.common.usage_request_handler.UsageRequestHandler instance') !== false);
    }

    /**
     * @test
     */
    public function it_can_load_available_devices()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'));
        $script = $cruiseSearch . "cruise_search._load_request_handler('App\\Organization', 1)" . PHP_EOL;
        $script .= "cruise_search.cabin_matcher.set_interval('2026-06-02', '2026-06-08')" . PHP_EOL;
        $script .= "print dumps(cruise_search.cabin_matcher.check())" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $expected = [
            [
                'available' => 2,
                'is_overbooked' => false,
                'usage_pairs' => [0 => 0],
                'device_id' => 6072,
            ],
            [
                'available' => 2,
                'is_overbooked' => false,
                'usage_pairs' => [0 => 0],
                'device_id' => 6073,
            ]
        ];
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals(array_keys($expected[$i]), array_keys($actual[$i]));
        }
    }

    /**
     * @test
     */
    public function it_can_load_price_modifiers()
    {
        $script = $this->prepareCruiseSearch(
            1,
            1,
            json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2027-01-01',
            '2027-12-31'
        );
        $script .= "cruise_search.get_cabins()" . PHP_EOL;
        $script .= "discounts = [str(x.get_id()) for x in cruise_search.price_modifiers[Config.PRICE_MODIFIER_TYPE_DISCOUNT]]" . PHP_EOL;
        $script .= "print '[' + ','.join(discounts) + ']'" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script, true, false);
        sort($actual);
        $cruiseDiscounts = PriceModifier::getModelPriceModifierIds(Cruise::class, 1);
        $this->assertEquals(array_slice($cruiseDiscounts, 0, 3), $actual);
    }

    /**
     * @test
     */
    public function it_can_get_open_and_price_modifier_date_ranges()
    {
        $script = $this->prepareCruiseSearch(1, 1, [], '2027-01-01', '2027-12-31');
        $script .= "print dumps(cruise_search._get_open_and_price_modifier_date_ranges('App\\Organization', 1))" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $expected = [
            164 => [
                [
                    'margin_type_taxonomy_id' => 57,
                    'minimum_nights' => null,
                    'type_taxonomy_id' => 164,
                    'name_description_id' => null,
                    'price_modifier_ids' => [13],
                    'from_time' => '2027-03-15 00:00:00',
                    'to_time' => '2027-03-23 23:59:59',
                    'margin_value' => null,
                    'id' => 13
                ],
                [
                    'margin_type_taxonomy_id' => 57,
                    'minimum_nights' => null,
                    'name_description_id' => null,
                    'price_modifier_ids' => [12],
                    'from_time' => '2027-03-24 00:00:00',
                    'type_taxonomy_id' => 164,
                    'to_time' => '2027-03-31 23:59:59',
                    'margin_value' => null,
                    'id' => 12
                ],
                [
                    'margin_type_taxonomy_id' => 57,
                    'minimum_nights' => null,
                    'name_description_id' => null,
                    'price_modifier_ids' => [8],
                    'from_time' => '2027-04-01 00:00:00',
                    'type_taxonomy_id' => 164,
                    'to_time' => '2027-05-01 23:59:59',
                    'margin_value' => null,
                    'id' => 9
                ],
                [
                    'margin_type_taxonomy_id' => 57,
                    'minimum_nights' => null,
                    'name_description_id' => null,
                    'price_modifier_ids' => [1, 2, 3, 4, 5, 6, 10],
                    'from_time' => '2027-06-01 00:00:00',
                    'type_taxonomy_id' => 164,
                    'to_time' => '2027-09-01 23:59:59',
                    'margin_value' => null,
                    'id' => 10
                ],
                [
                    'margin_type_taxonomy_id' => 57,
                    'minimum_nights' => null,
                    'name_description_id' => null,
                    'price_modifier_ids' => [7, 9, 10, 11],
                    'from_time' => '2027-09-02 00:00:00',
                    'type_taxonomy_id' => 164,
                    'to_time' => '2027-10-01 23:59:59',
                    'margin_value' => null,
                    'id' => 11
                ],
                [
                    'margin_type_taxonomy_id' => 57,
                    'minimum_nights' => null,
                    'name_description_id' => null,
                    'price_modifier_ids' => [14],
                    'from_time' => '2027-10-02 00:00:00',
                    'type_taxonomy_id' => 164,
                    'to_time' => '2027-11-01 23:59:59',
                    'margin_value' => null,
                    'id' => 14
                ]
            ],
            62 => [
                [
                    'margin_type_taxonomy_id' => 57,
                    'minimum_nights' => 3,
                    'name_description_id' => 18,
                    'from_time' => '2027-06-01 00:00:00',
                    'type_taxonomy_id' => 62,
                    'to_time' => '2027-09-01 23:59:59',
                    'margin_value' => 3.0,
                    'id' => 6
                ],
                [
                    'margin_type_taxonomy_id' => 57,
                    'minimum_nights' => 3,
                    'name_description_id' => 20,
                    'from_time' => '2027-09-02 00:00:00',
                    'type_taxonomy_id' => 62,
                    'to_time' => '2027-10-01 23:59:59',
                    'margin_value' => 3.0,
                    'id' => 8
                ],
            ],
        ];
        foreach ($expected as $k => $v) {
            for ($i = 0; $i < count($v); $i++) {
                $this->assertCount(count($v), $actual[$k]);
                $this->assertEquals($v[$i], $actual[$k][$i]);
            }
        }
    }

    /**
     * @test
     */
    public function it_can_get_date_ranges()
    {
        $cruiseSearch = $this->prepareCruiseSearch(1);

        $script = $cruiseSearch;
        $script .= "open_date_ranges = cruise_search._get_date_ranges('App\\Organization', 1, factory.datetime('2027-01-01'),factory.datetime('2027-12-31'), " . self::DATE_RANGE_TYPE_OPEN . ")" . PHP_EOL;
        $script .= "print jsonpickle.encode(open_date_ranges, unpicklable=False)" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $expected = [
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => 3,
                'type_taxonomy_id' => 62,
                'to_time' => '2027-09-01 23:59:59',
                'margin_value' => 3.0,
                'from_time' => '2027-06-01 00:00:00'
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => 3,
                'type_taxonomy_id' => 62,
                'to_time' => '2027-10-01 23:59:59',
                'margin_value' => 3.0,
                'from_time' => '2027-09-02 00:00:00',
            ]
        ];
        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals([], array_diff_assoc($expected[$i], $actual[$i][0]));
        }


        $script = $cruiseSearch;
        $script .= "closed_date_ranges = cruise_search._get_date_ranges('App\\Organization', 1, factory.datetime('2027-01-01'),factory.datetime('2027-12-31'), " . self::DATE_RANGE_TYPE_CLOSED . ")" . PHP_EOL;
        $script .= "print jsonpickle.encode(closed_date_ranges, unpicklable=False)" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $expected = [
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'type_taxonomy_id' => 63,
                'to_time' => '2027-08-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-07-11 00:00:00',
            ]
        ];
        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals([], array_diff_assoc($expected[$i], $actual[$i][0]));
        }

        $script = $cruiseSearch;
        $script .= "discount_date_ranges = cruise_search._get_date_ranges('App\\Organization', 1, factory.datetime('2027-01-01'),factory.datetime('2027-12-31'), " . self::DATE_RANGE_TYPE_PRICE_MODIFIER . ")" . PHP_EOL;
        $script .= "print jsonpickle.encode(discount_date_ranges, unpicklable=False)" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $expected = [
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'type_taxonomy_id' => 164,
                'name_description_id' => null,
                'from_time' => '2027-03-15 00:00:00',
                'to_time' => '2027-03-23 23:59:59',
                'margin_value' => null,
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'type_taxonomy_id' => 164,
                'name_description_id' => null,
                'from_time' => '2027-03-24 00:00:00',
                'to_time' => '2027-03-31 23:59:59',
                'margin_value' => null,
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'type_taxonomy_id' => 164,
                'to_time' => '2027-05-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-04-01 00:00:00',
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'type_taxonomy_id' => 164,
                'to_time' => '2027-09-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-06-01 00:00:00',
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'type_taxonomy_id' => 164,
                'to_time' => '2027-10-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-09-02 00:00:00',
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'type_taxonomy_id' => 164,
                'to_time' => '2027-11-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-10-02 00:00:00'
            ]
        ];

        $this->assertEquals(count($expected), count($actual));
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals([], array_diff_assoc($expected[$i], $actual[$i][0]));
        }
    }
}
