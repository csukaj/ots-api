<?php

namespace Tests\Procedure\Ots\Search;

use Tests\Procedure\ProcedureTestCase;

class RoomSearchTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.room_search import RoomSearch' . PHP_EOL .
    'from ots.common.config import Config' . PHP_EOL .
    'from json import dumps' . PHP_EOL .
    'import copy' . PHP_EOL;

    const DATE_RANGE_TYPE_OPEN = 62;
    const DATE_RANGE_TYPE_CLOSED = 63;
    const DATE_RANGE_TYPE_PRICE_MODIFIER = 164;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function prepareRoomSearch(
        $organizationId = 1,
        $request = [],
        $fromDate = null,
        $toDate = null,
        $bookingDate = null,
        $weddingDate = null
    ) {
        $roomSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $organizationId,
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
        return self::$imports . PHP_EOL . "room_search = RoomSearch({$roomSearchConfig})" . PHP_EOL;
    }

    /**
     * @test
     */
    public function it_can_init_itself()
    {
        $result = $this->runPythonScript($this->prepareRoomSearch());
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function it_can_get_rooms()
    {
        //no available_rooms
        $roomSearch = $this->prepareRoomSearch(1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'), '2010-06-02',
            '2010-06-08');
        $script = $roomSearch . "print room_search.get_rooms()" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertEquals('None', $actual);

        //has_available_rooms but no dates specified
        $roomSearch = $this->prepareRoomSearch(1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'));
        $script = $roomSearch . "print room_search.get_rooms()" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $this->assertArrayHasKey('availability', $actual);
        $this->assertCount(3, $actual['availability']);
        $this->assertArrayHasKey('results', $actual);
        $this->assertNull($actual['results']);

        //no or not enough result
        $roomSearch = $this->prepareRoomSearch(1, json_decode('[{"usage":[{ "age": 21, "amount": 15 }]}]'),
            '2010-06-02', '2010-06-08');
        $script = $roomSearch . "print room_search.get_rooms()" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertEquals('None', $actual);

        //good result
        $roomSearch = $this->prepareRoomSearch(1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'), '2026-06-02',
            '2026-06-08');
        $script = $roomSearch . "print room_search.get_rooms()" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $this->assertArrayHasKey('availability', $actual);
        $this->assertCount(3, $actual['availability']);
        $this->assertArrayHasKey('results', $actual);
        $this->assertCount(3, $actual['results'][0]);
    }

    /**
     * @test
     */
    public function it_can_calculate_device_prices()
    {
        $roomSearch = $this->prepareRoomSearch(1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'), '2026-06-02',
            '2026-06-08');
        $script = $roomSearch . "room_search.get_rooms()" . PHP_EOL;
        $script .= "print dumps(room_search._calculate_device_prices(0, room_search.available_devices[0]['device_id']))" . PHP_EOL;
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
        $roomSearch = $this->prepareRoomSearch(1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'), '2026-06-02',
            '2026-06-08');
        $script = $roomSearch . "room_search.get_rooms()" . PHP_EOL;
        $script .= "device = factory.device('App\\Organization', 1)" . PHP_EOL;
        $script .= "usage_price = room_search.price_search.find(
                0,
                room_search.request_handler.request[0],
                device['id'],
                None,
                room_search.date_ranges[Config.DATE_RANGE_TYPE_OPEN]
            )" . PHP_EOL;
        $script .= "print dumps(room_search._best_productable_prices([usage_price], 'device_id', device['id']))" . PHP_EOL;

        $actual = $this->runPythonAndDecodeJSON($script);
        $actual_without_unneeded = array_map(function($dict) {
            unset($dict['switches']);
            unset($dict['meal_offer']);
            return $dict;
        }, $actual['prices']);
        $expected = [
            [
                'original_price' => 660.0,
                'meal_plan_id' => 2,
                'discounted_price' => 660.0,
                'order_itemable_index' => 0,
                'meal_plan' => 'b/b',
                'discounts' => [],
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
                'margin' => 60
            ],
            [
                'original_price' => 666.6,
                'meal_plan_id' => 3,
                'discounted_price' => 666.6,
                'order_itemable_index' => 0,
                'meal_plan' => 'h/b',
                'discounts' => [],
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
                'margin' => 60.599999999999909
            ]
        ];
        $this->assertEquals($expected, $actual_without_unneeded,0.00001);
    }

    /**
     * @test
     */
    public function it_can_load_request_handler()
    {
        $roomSearch = $this->prepareRoomSearch(1, $this->scriptContainer('factory.request()'), '2026-06-02',
            '2026-06-08');
        $script = $roomSearch . "room_search._load_request_handler('App\\Organization', 1)" . PHP_EOL;
        $script .= "print room_search.request_handler" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertTrue(strpos($actual, '<ots.common.usage_request_handler.UsageRequestHandler instance') !== false);
    }

    /**
     * @test
     */
    public function it_can_load_available_devices()
    {
        $roomSearch = $this->prepareRoomSearch(1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'));
        $script = $roomSearch . "room_search._load_request_handler('App\\Organization', 1)" . PHP_EOL;
        $script .= "print dumps(room_search.room_matcher.check())" . PHP_EOL;
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
            ],
            [
                'available' => 2,
                'is_overbooked' => false,
                'usage_pairs' => [0 => 0],
                'device_id' => 6074,
            ],
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
        $script = $this->prepareRoomSearch(2, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'), '2027-06-02',
            '2027-06-08');
        $script .= "room_search.get_rooms()" . PHP_EOL;
        $script .= "print '[' + ','.join(str(x) for x in room_search.price_modifiers[Config.PRICE_MODIFIER_TYPE_DISCOUNT]) + ']'" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script, true, true);
        $this->assertCount(1, $actual); // only applicable discounts added to list
        foreach ($actual as $priceModifier) {
            $this->assertEquals(
                [],
                array_diff(
                    [
                        'classification',
                        'properties',
                        'price_modifiable_type',
                        'price_modifiable_id',
                        'meta',
                        'from_time',
                        'to_time'
                    ],
                    array_keys($priceModifier)
                )
            );
        }
    }

    /**
     * @test
     */
    public function it_can_get_open_and_price_modifier_date_ranges()
    {
        $script = $this->prepareRoomSearch(1, [], '2027-01-01', '2027-12-31');
        $script .= "print dumps(room_search._get_open_and_price_modifier_date_ranges('App\\Organization', 1))" . PHP_EOL;
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
                ]
            ],
        ];
        foreach ($expected as $date_range_tx_id => $date_ranges) {
            $this->assertCount(count($date_ranges), $actual[$date_range_tx_id]);
            for ($i = 0; $i < count($date_ranges); $i++) {
                $this->assertEquals($date_ranges[$i], $actual[$date_range_tx_id][$i]);
            }
        }
    }

    /**
     * @test
     */
    public function it_can_get_date_ranges()
    {
        $roomSearch = $this->prepareRoomSearch(1);

        $script = $roomSearch;
        $script .= "open_date_ranges = room_search._get_date_ranges('App\\Organization', 1, factory.datetime('2027-01-01'),factory.datetime('2027-12-31'), " . self::DATE_RANGE_TYPE_OPEN . ")" . PHP_EOL;
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


        $script = $roomSearch;
        $script .= "closed_date_ranges = room_search._get_date_ranges('App\\Organization', 1, factory.datetime('2027-01-01'),factory.datetime('2027-12-31'), " . self::DATE_RANGE_TYPE_CLOSED . ")" . PHP_EOL;
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

        $script = $roomSearch;
        $script .= "discount_date_ranges = room_search._get_date_ranges('App\\Organization', 1, factory.datetime('2027-01-01'),factory.datetime('2027-12-31'), " . self::DATE_RANGE_TYPE_PRICE_MODIFIER . ")" . PHP_EOL;
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
                'id' => 13,
                'price_modifier_ids' => [13]
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'type_taxonomy_id' => 164,
                'name_description_id' => null,
                'from_time' => '2027-03-24 00:00:00',
                'to_time' => '2027-03-31 23:59:59',
                'margin_value' => null,
                'id' => 12,
                'price_modifier_ids' => [12]
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'type_taxonomy_id' => 164,
                'to_time' => '2027-05-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-04-01 00:00:00',
                'id' => 9,
                'price_modifier_ids' => [8]
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'type_taxonomy_id' => 164,
                'to_time' => '2027-09-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-06-01 00:00:00',
                'id' => 10,
                'price_modifier_ids' => [1, 2, 3, 4, 5, 6, 10]
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'type_taxonomy_id' => 164,
                'to_time' => '2027-10-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-09-02 00:00:00',
                'id' => 11,
                'price_modifier_ids' => [7, 9, 10, 11]
            ],
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'type_taxonomy_id' => 164,
                'to_time' => '2027-11-01 23:59:59',
                'margin_value' => null,
                'from_time' => '2027-10-02 00:00:00',
                'id' => 14,
                'price_modifier_ids' => [14]
            ]
        ];

        $this->assertCount(count($expected), $actual);
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertEquals($expected[$i], $actual[$i][0]);
        }
    }
}
