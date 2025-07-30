<?php

namespace Tests\Procedure\Ots\Pricing;

use App\Organization;
use Tests\Procedure\ProcedureTestCase;

class RoomPriceSearchFuncTest extends ProcedureTestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;
    
    const DATE_RANGE_TYPE_OPEN = 62;
    const DATE_RANGE_TYPE_CLOSED = 63;
    const DATE_RANGE_TYPE_PRICE_MODIFIER = 164;

    public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->today = date('Y-m-d');
    }
    
    private function prepare($organizationId = 1, $deviceIndex = 0) {
        $this->organization = Organization::findOrFail($organizationId);
        $this->device = $this->organization->devices[$deviceIndex];
    }

    private function find($device_id, $usageJSON, $fromDate, $toDate, $bookingDate, $weddingDate = null, $resultNeeded = true) {
        $priceSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'from_time' => $fromDate,
            'to_time' => $toDate,
            'booking_time' => $bookingDate,
            'wedding_time' => $weddingDate,
            'remove_request' => $this->scriptContainer('True'),
            'settings' => $this->scriptContainer(json_encode([
                'discount_calculations_base' => 'rack prices',
                'merged_free_nights' => 'enabled'
            ])),
            'abstract_search' => $this->scriptContainer('factory.room_search(organization_id='.$this->organization->id.')')
        ]);
        $findConfig = $this->composeParams([
            0,
            $this->scriptContainer('request_handler.request[0]'),
            $device_id,
            null,
            $this->scriptContainer("factory.open_date_ranges({$this->organization->id})")
        ]);

        $script = <<<SCREND
from json import loads
from ots.common.usage_request_handler import UsageRequestHandler
request_handler = UsageRequestHandler(plpy_mocker, 'App\\Organization', 1)
request_handler.set_request(loads('{$usageJSON}'))

from ots.pricing.room_price_search import RoomPriceSearch
search = RoomPriceSearch({$priceSearchConfig})
device_prices = search.find($findConfig)
SCREND;

        if ($resultNeeded) {
            $script .= PHP_EOL . "print dumps(device_prices)" . PHP_EOL;
        }
        $result = $this->runPythonScript($script);
        $decoded = \json_decode($result, true);
        return ($resultNeeded && !empty($decoded)) ? $decoded : $result;
    }

    private function findPriceForMealPlan($result, $mealPlanId) {
        if (!empty($result['prices'])) {
            foreach ($result['prices'] as $value) {
                if ($value['meal_plan_id'] == (string)$mealPlanId) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * @test
     */
    function it_can_get_some_basic_prices() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}, {"age": 1, "amount": 1}]}]';
        $this->prepare();
        $result = $this->find($this->device->id, $usageJson, '2026-06-03', '2026-06-13', $this->today);
        $this->assertEquals(1320, $this->findPriceForMealPlan($result, 2)['discounted_price']);
        $this->assertEquals(1342, $this->findPriceForMealPlan($result, 3)['discounted_price']);
    }

    /**
     * @test
     */
    function it_can_get_some_prices_with_wedding_date() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}, {"age":1, "amount":1}]}]';
        $this->prepare();
        $result = $this->find($this->device->id, $usageJson, '2026-06-03', '2026-06-13', $this->today, '1990-01-01');
        $this->assertEquals(1320, $this->findPriceForMealPlan($result, 2)['discounted_price']);
        $this->assertEquals(1342, $this->findPriceForMealPlan($result, 3)['discounted_price']);
    }

    /**
     * @test
     */
    function it_can_get_prices_from_two_date_ranges() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}]}]';
        $this->prepare();
        $result = $this->find($this->device->id, $usageJson, '2026-08-30', '2026-09-06', $this->today);
        $this->assertEquals(778.8, $this->findPriceForMealPlan($result, 2)['discounted_price']);
    }

    /**
     * @test
     */
    function it_can_get_prices_with_priceModifier() {
        $this->markTestIncomplete('needs combination_wrapper passed over to be tested');
        $usageJson = '[{"usage": [{"age":21, "amount":1}]}]';
        $this->prepare();
        $result = $this->find($this->device->id, $usageJson, '2027-06-03', '2027-06-13', $this->today);
        /** @todo needs combination_wrapper passed over to be tested */
        $this->assertLessThan(1100, $this->findPriceForMealPlan($result, 2)['discounted_price']);
        $this->assertLessThan(1133, $this->findPriceForMealPlan($result, 3)['discounted_price']);
        $this->assertGreaterThan(0, count($this->findPriceForMealPlan($result, 2)['discounts']));
        $this->assertGreaterThan(0, count($this->findPriceForMealPlan($result, 3)['discounts']));

        $this->assertGreaterThan(1, count($result['prices']));
        foreach ($result['prices'] as $item) {
            $this->assertArrayHasKey('total_discount', $item);
            $this->assertArrayHasKey('percentage', $item['total_discount']);
            $this->assertLessThan(0, $item['total_discount']['percentage']);
            $this->assertArrayHasKey('value', $item['total_discount']);
            $this->assertLessThan(0, $item['total_discount']['value']);
            $this->assertEquals($item['discounted_price'] - $item['original_price'], $item['total_discount']['value']);
        }
    }

    /**
     * @test
     */
    function it_can_get_prices_from_two_date_ranges_with_priceModifier() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}]}]';
        $this->markTestIncomplete('needs combination_wrapper passed over to be tested');
        $this->prepare();
        $result = $this->find($this->device->id, $usageJson, '2027-08-30', '2027-09-03', $this->today);
        /** @todo needs combination_wrapper passed over to be tested */
        $this->assertLessThan(550, $this->findPriceForMealPlan($result, 2)['discounted_price']);
        $this->assertLessThan(563.2, $this->findPriceForMealPlan($result, 3)['discounted_price']);
        $this->assertGreaterThan(0, count($this->findPriceForMealPlan($result, 2)['discounts']));
        $this->assertGreaterThan(0, count($this->findPriceForMealPlan($result, 3)['discounts']));
    }

    /**
     * @test
     */
    function it_works_when_no_extra_price_found() {
        $usageJson = '[{"usage":[{"age":21,"amount":2}]},{"usage":[{"age":21,"amount":1}]}]';
        $this->prepare();
        $result = $this->find($this->device->id, $usageJson, '2026-07-06', '2026-07-08', $this->today);
        $this->assertEquals(0, count($result['prices']));
    }
    
    /**
     * @test
     */
    function it_counts_zero_for_free_age_ranges() {
        $this->prepare(15, 1);
        
        $usageJson1 = '[{"usage":[{"age":21, "amount":2}]}]';
        $result1 = $this->find($this->device->id, $usageJson1, '2027-07-06', '2027-07-08', $this->today);
        $this->assertEquals(400, $this->findPriceForMealPlan($result1, 2)['original_price']);
        
        $usageJson2 = '[{"usage":[{"age": 21, "amount": 1}, {"age": 16, "amount": 1}]}]';
        $result2 = $this->find($this->device->id, $usageJson2, '2027-07-06', '2027-07-08', $this->today);
        $this->assertEquals(200, $this->findPriceForMealPlan($result2, 2)['original_price']);
    }
}