<?php

namespace Tests\Procedure\Ots\Search;

use Tests\Procedure\ProcedureTestCase;

class CharterSearchTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.charter_search import CharterSearch' . PHP_EOL .
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

    private function prepareCharterSearch(
        $organizationId,
        $organizationGroupId,
        $request,
        $fromDate = null,
        $toDate = null,
        $bookingDate = null,
        $weddingDate = null
    ) {
        $searchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $organizationId,
            'organization_group_id' => $organizationGroupId,
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
        return self::$imports . PHP_EOL . "charter_search = CharterSearch({$searchConfig})" . PHP_EOL;
    }

    /**
     * @test
     */
    public function it_can_init_itself()
    {
        $result = $this->runPythonScript($this->prepareCharterSearch(301, 1,
            json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]')));
        echo $result;
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function it_can_get_charters()
    {
        // no available charters
        $roomSearch = $this->prepareCharterSearch(301, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2010-06-02', '2010-06-08');
        $script = $roomSearch . "print charter_search.get_charters()" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertEquals('None', $actual);

        // has available charters but no dates specified
        $roomSearch = $this->prepareCharterSearch(301, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'));
        $script = $roomSearch . "print charter_search.get_charters()" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $this->assertArrayHasKey('availability', $actual);
        $this->assertSame([
            'ship_count',
            'name_description_id',
            'is_overbooked',
            'id',
            'required_ship_count'
        ], array_keys($actual['availability']));
        $this->assertArrayHasKey('results', $actual);
        $this->assertNull($actual['results']);

        // no or not enough result
        $roomSearch = $this->prepareCharterSearch(301, 1, json_decode('[{"usage":[{ "age": 21, "amount": 15 }]}]'),
            '2010-06-02', '2010-06-08');
        $script = $roomSearch . "print charter_search.get_charters()" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertEquals('None', $actual);

        // good result
        $roomSearch = $this->prepareCharterSearch(301, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2026-06-02', '2026-06-08');
        $script = $roomSearch . "print charter_search.get_charters()" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);
        $this->assertArrayHasKey('availability', $actual);
        $this->assertSame([
            'ship_count',
            'name_description_id',
            'is_overbooked',
            'id',
            'required_ship_count'
        ], array_keys($actual['availability']));
        $this->assertArrayHasKey('results', $actual);
        $this->assertCount(1, $actual['results']);
    }

    /**
     * @test
     */
    public function it_can_calculate_charter_prices()
    {
        $script = "from ots.price_modifier.combination_wrapper import CombinationWrapper" . PHP_EOL;
        $script .= $this->prepareCharterSearch(301, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2026-06-02', '2026-06-08');
        $script .= "organization_group_id = charter_search.organization_group_id" . PHP_EOL;
        $script .= "charter_search.date_ranges = charter_search._get_open_and_price_modifier_date_ranges('App\\\\ShipGroup', organization_group_id)" . PHP_EOL;
        $script .= "print dumps(charter_search._calculate_charter_prices(0, organization_group_id))" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script);

        $this->assertArrayHasKey('ship_group_id', $actual);
        $this->assertArrayHasKey('prices', $actual);
        $this->assertTrue(is_array($actual['prices']));
        $this->assertCount(2, $actual['prices']);
        $this->assertArrayHasKey('usages', $actual);
        $this->assertTrue(is_array($actual['usages']));
        $this->assertEquals(['adult' => 1], $actual['usages']);
    }

    /**
     * @test
     */
    public function it_can_get_best_charter_prices()
    {
        $script = $this->prepareCharterSearch(301, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2026-06-02', '2026-06-08');
        $script .= "charter_search.get_charters()" . PHP_EOL;
        $script .= "ship_group = charter_search.ship_group" . PHP_EOL;
        $script .= "date_ranges = charter_search._get_open_and_price_modifier_date_ranges('App\\ShipGroup', ship_group['id'])" . PHP_EOL;
        $script .= "usage_price = charter_search.price_search.find(
                0,
                charter_search.request_handler.request[0],
                ship_group['id'],
                None,
                date_ranges[Config.DATE_RANGE_TYPE_OPEN]
            )" . PHP_EOL;
        $script .= "print dumps(charter_search._best_productable_prices([usage_price], 'ship_group_id', ship_group['id']))" . PHP_EOL;

        $actual = $this->runPythonAndDecodeJSON($script);

        $actual_without_unneeded = array_map(function($dict) {
            unset($dict['switches']);
            unset($dict['meal_offer']);
            return $dict;
        }, $actual['prices']);
        $expected = [
            [
                'original_price' => '15366.0',
                'meal_plan_id' => 1,
                'discounted_price' => '15366.0',
                'order_itemable_index' => 0,
                'meal_plan' => 'e/p',
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
                'margin' => 2004.2608695653998
            ],
            [
                'original_price' => '15366.0',
                'meal_plan_id' => 4,
                'discounted_price' => '15366.0',
                'order_itemable_index' => 0,
                'meal_plan' => 'f/b',
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
                'margin' => 2004.2608695653998
            ]
        ];
        $this->assertEquals($expected, $actual_without_unneeded);
    }

    /**
     * @test
     */
    public function it_can_load_request_handler()
    {
        $roomSearch = $this->prepareCharterSearch(301, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'),
            '2026-06-02', '2026-06-08');
        $script = $roomSearch . "charter_search._load_request_handler('App\\Organization', 1)" . PHP_EOL;
        $script .= "print charter_search.request_handler" . PHP_EOL;
        $actual = $this->runPythonScript($script);
        $this->assertTrue(strpos($actual, '<ots.common.usage_request_handler.UsageRequestHandler instance') !== false);
    }

    /**
     * @test
     */
    public function it_can_get_date_ranges()
    {
        $roomSearch = $this->prepareCharterSearch(301, 1, json_decode('[{"usage":[{ "age": 21, "amount": 1 }]}]'));

        $script = $roomSearch;
        $script .= "open_date_ranges = charter_search._get_date_ranges('App\\\\ShipGroup', 1, factory.datetime('2026-01-01'),factory.datetime('2027-12-31'), " . self::DATE_RANGE_TYPE_OPEN . ")" . PHP_EOL;
        $script .= "print str(open_date_ranges)" . PHP_EOL;
        $actual = $this->runPythonAndDecodeJSON($script, true, true);
        foreach ($actual as $row) {
            $this->assertNotEmpty($row['id']);
            $this->assertNotEmpty($row['name_description_id']);
            $this->assertNotEmpty($row['type_taxonomy_id']);
            $this->assertNotEmpty($row['from_time']);
            $this->assertNotEmpty($row['to_time']);
            $this->assertNotEmpty($row['margin_value']);
            $this->assertArrayHasKey('margin_type_taxonomy_id', $row);
            $this->assertNotEmpty($row['minimum_nights']);
        }
    }
}
