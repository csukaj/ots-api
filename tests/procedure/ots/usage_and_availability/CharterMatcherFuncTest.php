<?php

namespace Tests\Procedure\Ots;

use App\OrganizationGroup;
use App\ShipGroup;
use Tests\Procedure\ProcedureTestCase;

class CharterMatcherFuncTest extends ProcedureTestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private $expectedResult = [
        'id' => 1,
        'name_description_id' => null,
        'is_overbooked' => false,
        'ship_count' => 2,
        'required_ship_count' => 1.0
    ];

    private function getScript($organizationId, $organizationGroupId, $usageJson, $fromDate, $toDate, $script)
    {
        if (!$this->expectedResult['name_description_id']) {
            $this->expectedResult['name_description_id'] = OrganizationGroup::find($organizationGroupId)->name_description_id;
        }

        $params = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'request_handler' => $this->scriptContainer('request_handler'),
            'organization_id' => $organizationId,
            'organization_group_id' => $organizationGroupId,
            'show_inactive' => $this->scriptContainer( 'False')
        ]);

        $fromDate = $fromDate ? "'{$fromDate}'" : 'None';
        $toDate = $toDate ? "'{$toDate}'" : 'None';

        $initScript = <<<"EOF"
from json import loads
from ots.common.usage_request_handler import UsageRequestHandler
request_handler = UsageRequestHandler(plpy_mocker, 'App\\Organization', {$organizationId})
request_handler.set_request(loads('{$usageJson}'))

from ots.usage_and_availability.charter_matcher import CharterMatcher
charter_matcher = CharterMatcher({$params})
charter_matcher.set_interval({$fromDate}, {$toDate})
EOF;

        return $initScript . "\n" . $script;
    }

    private function getAvailability(
        $organizationId,
        $organizationGroupId,
        $usageJson,
        $fromDate,
        $toDate,
        $resultNeeded = true
    ) {
        $script = $this->getScript($organizationId, $organizationGroupId, $usageJson, $fromDate, $toDate,
            'availabile_devices = charter_matcher.check()');

        if ($resultNeeded) {
            $script .= PHP_EOL . 'print availabile_devices';
        }
        $result = $this->runPythonScript($script);
        $decoded = $this->jsonDecode($result, true);
        return $resultNeeded ? $decoded : $result;
    }

    /**
     * @test
     */
    function it_can_get_ship_group()
    {
        $script = $this->getScript(
            301,
            1,
            '[{"usage": [{"age":21, "amount":2}]}]',
            '2026-06-30',
            '2026-07-05',
            'print charter_matcher._get_ship_group()'
        );
        $actual = $this->jsonDecode($this->runPythonScript($script), true);
        $shipGroup = ShipGroup::findOrFail(1);
        $expected = [
            'id' => $shipGroup->id,
            'name_description_id' => $shipGroup->name_description_id,
            'ship_count' => 2
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    function it_can_instantiate()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-06-30';
        $toDate = '2026-07-05';

        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate, false);
        $this->assertEmpty($result, $result);
    }

    /**
     * @test
     */
    public function it_can_find_a_single_interval()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-06-30';
        $toDate = '2026-07-05';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_cannot_find_a_nonexistent_interval()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2015-06-30';
        $toDate = '2015-07-05';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_can_not_find_any_interval_with_too_high_demand()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":30}]}]';
        $fromDate = '2026-06-10';
        $toDate = '2026-06-15';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_can_find_an_interval_with_exact_match_to_borders()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_can_find_usage_with_child()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":1}, {"age":5, "amount":1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_can_find_usage_with_two_children()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":1}, {"age":5, "amount":1}, {"age":4, "amount":1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_can_find_usage_with_ten_children()
    {
        $usageJson = '[{"usage": [
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1}
        ]}]';
        $usageJson = preg_replace('/\s+/', '', $usageJson);
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';

        $this->markTestSkipped('If child bed policy is not strict, the tst is good. else chartermatcher shoul be modified');

        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_cannot_find_usage_with_eleven_children()
    {
        $usageJson = '[{"usage": [
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1},
            {"age":6, "amount":1}
        ]}]';
        $usageJson = preg_replace('/\s+/', '', $usageJson);
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';

        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_can_find_usage_for_three_children()
    {
        $usageJson = '[{"usage": [{"age":5, "amount":1}, {"age":4, "amount":1}, {"age":6, "amount":1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-01';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals([
            'id' => 1,
            'name_description_id' => 431,
            'is_overbooked' => false,
            'ship_count' => 2,
            'required_ship_count' => 2.0
        ], $result);
    }

    /**
     * @test
     */
    public function it_can_find_multiple_interval_periods()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-06-10';
        $toDate = '2026-07-10';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_can_find_multiple_interval_periods_at_borders()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-06-30';
        $toDate = '2026-07-06';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_does_not_match_on_availability_gaps()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-03-20';
        $toDate = '2026-05-20';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function it_matches_over_multiple_intervals()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-08-20';
        $toDate = '2026-09-20';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_is_restricted_by_minimum_nights()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2027-06-01';
        $toDate = '2027-06-03';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertNull($result);

        $toDate2 = '2027-06-04';
        $result2 = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate2);
        $this->assertEquals($this->expectedResult, $result2);
    }

    /**
     * @test
     */
    public function it_can_find_cooked_ranges()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2028-06-01';
        $toDate = '2028-06-08';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_is_restricted_by_banned_age_ranges()
    {
        $adultUsageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $childUsageJson = '[{"usage": [{"age": 12, "amount": 1}]}]';
        $babyUsageJson = '[{"usage": [{"age": 1, "amount": 1}]}]';
        $fromDate = '2027-06-10';
        $toDate = '2027-06-16';
        $result = $this->getAvailability(301, 1, $adultUsageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
        $this->assertEquals($this->expectedResult, $this->getAvailability(301, 1, $childUsageJson, $fromDate, $toDate));
        $this->assertNull($this->getAvailability(301, 1, $babyUsageJson, $fromDate, $toDate));
    }

    /**
     * @test
     */
    public function it_respects_device_minimum_nights_when_only_one_range_matches()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-06-05';
        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);

        $result2 = $this->getAvailability(301, 1, $usageJson, $fromDate, '2026-06-06');
        $this->assertEquals($this->expectedResult, $result2);

        $result3 = $this->getAvailability(301, 1, $usageJson, $fromDate, '2026-06-07');
        $this->assertEquals($this->expectedResult, $result3);
    }

    /**
     * @test
     */
    public function it_respects_device_minimum_nights_when_on_range_border_and_its_passes_in_both_ranges()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-08-25';
        $toDate = '2026-09-07';

        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

    /**
     * @test
     */
    public function it_respects_device_minimum_nights_when_on_range_border_and_passes_only_when_ranges_combined()
    {
        $usageJson = '[{"usage": [{"age": 21, "amount": 1}]}]';
        $fromDate = '2026-08-27';
        $toDate = '2026-09-04';

        $result = $this->getAvailability(301, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals($this->expectedResult, $result);
    }

}
