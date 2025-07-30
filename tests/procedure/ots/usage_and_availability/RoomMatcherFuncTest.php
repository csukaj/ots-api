<?php

namespace Tests\Procedure\Ots;

use Tests\Procedure\ProcedureTestCase;

class RoomMatcherFuncTest extends ProcedureTestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function commonTests($expectedRows, $usageCounts, $result, $usageJson = '') {
        $this->assertEquals($expectedRows, count($result));
        $this->assertEquals($expectedRows, count($usageCounts));

        $roomRequest = \json_decode($usageJson);
        $roomRequestKeys = array_keys($roomRequest);

        foreach ($result as $row) {
            $this->assertTrue(!!$row->available);
            $this->assertTrue(is_array($row->usage_pairs));
            $this->assertNotEmpty($row->usage_pairs);
            $this->assertTrue(!!$row->device_id);
            $this->assertNotEmpty(count(array_intersect($roomRequestKeys, $row->usage_pairs)));
        }
    }

    private function getAvailability($organizationId, $usageJson, $fromDate, $toDate, $resultNeeded = true) {
        $params = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'request_handler' => $this->scriptContainer('request_handler'),
            'organization_id' => $organizationId,
            'show_inactive' => $this->scriptContainer('False')
        ]);

        $fromDate = $fromDate ? "'{$fromDate}'" : 'None';
        $toDate = $toDate ? "'{$toDate}'" : 'None';

        $script = <<<"EOF"
from json import loads
from ots.common.usage_request_handler import UsageRequestHandler
request_handler = UsageRequestHandler(plpy_mocker, 'App\\Organization', {$organizationId})
request_handler.set_request(loads('{$usageJson}'))

from ots.usage_and_availability.room_matcher import RoomMatcher
usage = RoomMatcher({$params})
usage.set_interval({$fromDate}, {$toDate})
availabile_devices = usage.check()
EOF;

        if ($resultNeeded) {
            $script .= PHP_EOL . "from json import dumps" . PHP_EOL;
            $script .= "print dumps(availabile_devices)" . PHP_EOL;
        }
        $result = $this->runPythonScript($script);
        $decoded = \json_decode($result);
        return ($resultNeeded && !empty($decoded)) ? $decoded : $result;
    }

    /**
     * @test
     */
    function it_can_instantiate() {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-06-30';
        $toDate = '2026-07-05';

        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate, false);
        $this->assertEmpty($result, $result);
    }

    /**
     * @test
     */
    public function it_can_find_a_single_interval() {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-06-30';
        $toDate = '2026-07-05';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(2, [1, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_cannot_find_a_nonexistent_interval() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2015-06-30';
        $toDate = '2015-07-05';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->assertEquals('null', $result);
    }

    /**
     * @test
     */
    public function it_can_find_an_infinite_interval() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-09-30';
        $toDate = '2026-10-07';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_find_an_interval_with_multiple_room_demand() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}]}, {"usage": [{"age":21, "amount":1}]}]';
        $fromDate = '2026-09-02';
        $toDate = '2026-09-12';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_find_an_interval_with_multiple_room_types() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}]}, {"usage": [{"age":21, "amount":1}]}]';
        $fromDate = '2026-06-10';
        $toDate = '2026-06-16';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_not_find_any_interval_with_too_high_demand() {
        $usageJson = '[{"usage": [{"age":21, "amount":3}]}]';
        $fromDate = '2026-06-10';
        $toDate = '2026-06-15';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->assertEquals('null', $result);
    }

    /**
     * @test
     */
    public function it_can_find_an_interval_with_exact_match_to_borders() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_find_usage_with_child() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}, {"age":5, "amount":1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_find_usage_with_two_children() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}, {"age":5, "amount":1}, {"age":4, "amount":1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(2, [1, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_find_usage_with_one_adult_two_children_and_a_baby() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}, {"age":5, "amount":1}, {"age":4, "amount":1}, {"age":1, "amount":1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(1, [1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_cannot_find_usage_with_three_children() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}, {"age":5, "amount":1}, {"age":4, "amount":1}, {"age":6, "amount":1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';

        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->assertEquals('null', $result);
    }

    /**
     * @test
     */
    public function it_can_find_usage_for_three_children() {
        $usageJson = '[{"usage": [{"age":5, "amount":1}, {"age":4, "amount":1}, {"age":6, "amount":1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(2, [1, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_find_multiple_interval_periods() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-06-10';
        $toDate = '2026-07-10';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_find_multiple_interval_periods_at_borders() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-06-30';
        $toDate = '2026-07-06';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_does_not_match_on_availability_gaps() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-03-20';
        $toDate = '2026-05-20';
        $result = $this->getAvailability(8, $usageJson, $fromDate, $toDate);
        $this->assertEquals('null', $result);
    }

    /**
     * @test
     */
    public function it_matches_over_multiple_intervals() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-08-20';
        $toDate = '2026-09-20';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [2, 2, 2], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_does_not_match_on_default_interval_if_there_are_not_enough_rooms() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}]}, {"usage": [{"age":21, "amount":1}]}, {"usage": [{"age":21, "amount":1}]}]';
        $fromDate = null;
        $toDate = null;
        $result = $this->getAvailability(7, $usageJson, $fromDate, $toDate);
        $this->assertEquals('null', $result);
    }

    /**
     * @test
     */
    public function it_matches_for_different_available_rooms_without_interval() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}]}, {"usage": [{"age":21, "amount":1}]}, {"usage": [{"age":21, "amount":1}]}]';
        $fromDate = null;
        $toDate = null;
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);

        $usageJson2 = '[{"usage": [{"age":21, "amount":1}]}, {"usage": [{"age":21, "amount":1}]}]';
        $result2 = $this->getAvailability(2, $usageJson2, $fromDate, $toDate);
        $this->commonTests(2, [1, 1], $result2, $usageJson2);
    }

    /**
     * @test
     */
    public function it_can_find_multiple_device_for_a_single_interval() {
        $usageJson = '[{"usage": [{"age":21, "amount":1}]}, {"usage": [{"age":21, "amount":1}]}]';
        $fromDate = '2026-06-25';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_is_restricted_by_minimum_nights() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2027-06-01';
        $toDate = '2027-06-03';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->assertEquals('null', $result);

        $toDate2 = '2027-06-04';
        $result2 = $this->getAvailability(1, $usageJson, $fromDate, $toDate2);
        $this->commonTests(3, [1, 2, 1], $result2, $usageJson);
    }

    /**
     * @test
     */
    public function it_can_find_cooked_ranges() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2028-06-01';
        $toDate = '2028-06-08';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }
    
    /**
     * @test
     */
    public function it_is_restricted_by_banned_age_ranges() {
        $adultUsageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $adolescentUsageJson = '[{"usage": [{"age": 16, "amount": 1}]}]';
        $childUsageJson = '[{"usage": [{"age": 12, "amount": 1}]}]';
        $fromDate = '2027-06-10';
        $toDate = '2027-06-16';
        $this->commonTests(2, [0, 0], $this->getAvailability(15, $adultUsageJson, $fromDate, $toDate), $adultUsageJson);
        $this->commonTests(2, [0, 0], $this->getAvailability(15, $adolescentUsageJson, $fromDate, $toDate), $adolescentUsageJson);
        $this->assertEquals('null', $this->getAvailability(15, $childUsageJson, $fromDate, $toDate));
    }
    
    /**
     * @test
     */
    public function it_respects_device_minimum_nights_when_only_one_range_matches() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-06-05';
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(1, [1], $result, $usageJson);
        
        $result2 = $this->getAvailability(1, $usageJson, $fromDate, '2026-06-06');
        $this->commonTests(2, [2, 1], $result2, $usageJson);
        
        $result3 = $this->getAvailability(1, $usageJson, $fromDate, '2026-06-07');
        $this->commonTests(3, [1, 2, 1], $result3, $usageJson);
    }
    
    /**
     * @test
     */
    public function it_respects_device_minimum_nights_when_on_range_border_and_its_passes_in_both_ranges() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-08-25';
        $toDate = '2026-09-07';
        
        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }
    
    /**
     * @test
     */
    public function it_respects_device_minimum_nights_when_on_range_border_and_passes_only_when_ranges_combined() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-08-27';
        $toDate = '2026-09-04';

        $result = $this->getAvailability(1, $usageJson, $fromDate, $toDate);
        $this->commonTests(3, [1, 2, 1], $result, $usageJson);
    }

    /**
     * @test
     */
    public function it_respects_strict_child_bed_policy() {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1},{"age": 7, "amount": 1},{"age": 8, "amount": 1}]}]';
        $fromDate = '2026-07-10';
        $toDate = '2026-07-15';

        $result = $this->getAvailability(13, $usageJson, $fromDate, $toDate);
        $this->commonTests(1, [2], $result, $usageJson);
    }

}
