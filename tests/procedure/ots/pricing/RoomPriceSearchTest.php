<?php

namespace Tests\Procedure\Ots\Pricing;

use Tests\Procedure\ProcedureTestCase;

class RoomPriceSearchTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.pricing.room_price_search import RoomPriceSearch' . PHP_EOL . 'from json import dumps' . PHP_EOL;

    private function prepareAndRun($script)
    {
        $config = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => '2027-06-01 12:00:00',
            'to_time' => '2027-06-10 12:00:00',
            'booking_time' => '2019-01-10 12:00:00',
            'wedding_time' => '2017-01-01 12:00:00',
            'remove_request' => $this->scriptContainer('True'),
            'settings' => $this->scriptContainer(json_encode([
                'discount_calculations_base' => 'rack prices',
                'merged_free_nights' => 'enabled'
            ])),
            'abstract_search' => $this->scriptContainer('factory.room_search()')
        ]);
        return $this->runPythonScript(self::$imports . "price_search = RoomPriceSearch({$config})" . PHP_EOL . $script);
    }

    private function prepareFind($organizationId = 1, $combinationWrapper = 'None')
    {
        $findParams = [
            0, //order_itemable_index,
            $this->scriptContainer('factory.room_request()'), //room_request,
            $this->scriptContainer("factory.device('App\\Organization', {$organizationId})['id']"), //device_id,
            $this->scriptContainer($combinationWrapper), //combination_wrapper,
            $this->scriptContainer("factory.open_date_ranges({$organizationId})"), //open_date_ranges
        ];
        $config = $this->composeParams($findParams);
        return "result = price_search.find({$config})";
    }

    /**
     * @test
     */
    public function it_can_init_itself()
    {
        $this->assertEquals('', $this->prepareAndRun(''));
    }

    /**
     * @test
     */
    public function it_can_find()
    {
        $actual = $this->jsonDecode($this->prepareAndRun(
            "import json" . PHP_EOL . 
            $this->prepareFind() . PHP_EOL . 
            'print json.dumps(result)'
        ), true);
        $expected = [
            'prices' => [
                [
                    'original_price' => '0',
                    'meal_plan_id' => 3,
                    'has_merged_free_nights' => false,
                    'order_itemable_index' => 0,
                    'discounts' => [],
                    'discounted_price' => '0.0',
                    'margin' => 0.0,
                    'room_request' => [],
                    'meal_plan' => 'h/b',
                    'period' => []
                ],
                [
                    'original_price' => '0',
                    'meal_plan_id' => 2,
                    'has_merged_free_nights' => false,
                    'order_itemable_index' => 0,
                    'discounts' => [],
                    'discounted_price' => '0.0',
                    'margin' => 0.0,
                    'room_request' => [],
                    'meal_plan' => 'b/b',
                    'period' => []
                ]
            ],
            'usages' => ['adult' => 1, 'child' => 1]
        ];

        $this->assertTrue(isset($actual['prices']));
        $this->assertTrue(isset($actual['usages']));
        $this->assertEquals(count($expected['prices']), count($actual['prices']));
        for ($i = 0; $i < count($expected['prices']); $i++) {
            $a = array_keys($expected['prices'][$i]);
            $b = array_filter(array_keys($actual['prices'][$i]), function ($item) {
                return $item != "meal_offer" && $item != "switches";
            });


            sort($a);
            sort($b);
            $this->assertEquals($a, $b);
        }
        $this->assertEqualArrayContents($expected['usages'], $actual['usages']);
    }

    /**
     * @test
     */
    public function it_can_load_device()
    {
        $actual = $this->jsonDecode($this->prepareAndRun(
            $this->prepareFind() . PHP_EOL .
            "price_search._load_device()" . PHP_EOL .
            "print dict(price_search.device)" . PHP_EOL
        ), true);

        $columns = [
            "margin_type_taxonomy_id",
            "amount",
            "created_at",
            "updated_at",
            "deviceable_type",
            "deviceable_id",
            "type_taxonomy_id",
            "deleted_at",
            "margin_value",
            "id",
            "name_taxonomy_id"
        ];
        $this->assertEquals([], array_diff($columns, array_keys($actual)));
    }

    /**
     * @test
     */
    public function it_can_get_offers()
    {
        $actual = $this->jsonDecode($this->prepareAndRun(
            $this->prepareFind() . PHP_EOL .
            "import json" . PHP_EOL . 
            "print json.dumps(price_search._get_offers())" . PHP_EOL
        ), true);
        $this->assertGreaterThan(0, count($actual['prices']));
        $this->assertUnorderedMultidimensionalSetsEquals([
            'original_price',
            'meal_plan_id',
            'has_merged_free_nights',
            'order_itemable_index',
            'discounts',
            'discounted_price',
            'margin',
            'room_request',
            'meal_plan',
            'period',
            'switches',
            'meal_offer'
        ], array_keys($actual['prices'][0]));
    }

}
