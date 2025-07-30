<?php

namespace Tests\Procedure\Ots\PriceModifier;

use Tests\Procedure\ProcedureTestCase;

class CruiseMatcherFuncTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function getAvailability($organizationId, $cruiseId, $usageJson, $fromDate, $toDate, $resultNeeded = true)
    {
        $params = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'request_handler' => $this->scriptContainer('request_handler'),
            'organization_id' => $organizationId,
            'cruise_id' => $cruiseId,
            'show_inactive' => 'False'
        ]);

        $fromDate = $fromDate ? "'{$fromDate}'" : 'None';
        $toDate = $toDate ? "'{$toDate}'" : 'None';

        $script = <<<"EOF"
from json import loads
from ots.common.usage_request_handler import UsageRequestHandler
request_handler = UsageRequestHandler(plpy_mocker, 'App\\Organization', {$organizationId})
request_handler.set_request(loads('{$usageJson}'))

from ots.usage_and_availability.cruise_matcher import CruiseMatcher
usage = CruiseMatcher({$params})
usage.set_interval({$fromDate}, {$toDate})
availabile_devices = usage.check()
EOF;

        if ($resultNeeded) {
            $script .= PHP_EOL . "from json import dumps" . PHP_EOL;
            $script .= "print dumps(availabile_devices)" . PHP_EOL;
        }
        $result = $this->runPythonScript($script);
        $decoded = \json_decode($result, true);
        return ($resultNeeded && !\json_last_error()) ? $decoded : $result;
    }

    /**
     * @test
     */
    function it_can_instantiate()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-06-30';
        $toDate = '2026-07-05';

        $result = $this->getAvailability(1, 1, $usageJson, $fromDate, $toDate, false);
        $this->assertEmpty($result, $result);
    }

    /**
     * @test
     */
    public function it_can_find_a_single_schedule_with_multiple_start_dates()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-07-05';
        $result = $this->getAvailability(1, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals([
            [
                'dates' => [
                    '2026-06-01',
                    '2026-06-08',
                    '2026-06-15',
                    '2026-06-22',
                    '2026-06-29'
                ],
                'embarkation_time' => [
                    'time' => '08:00:00',
                    'precision_taxonomy_id' => 339,
                    'time_of_day_taxonomy_id' => null
                ],
                'id' => 1,
                'technical_length_days' => 4,
                'financial_length_days' => 4,
                'financial_offset_days' => 0
            ]
        ], $result);
    }

    /**
     * @test
     */
    public function it_can_find_a_single_schedule_with_a_single_start_date()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-06-01';
        $toDate = '2026-06-04';
        $result = $this->getAvailability(1, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals([
            [
                'dates' => [
                    '2026-06-01'
                ],
                'embarkation_time' => [
                    'time' => '08:00:00',
                    'precision_taxonomy_id' => 339,
                    'time_of_day_taxonomy_id' => null
                ],
                'id' => 1,
                'technical_length_days' => 4,
                'financial_length_days' => 4,
                'financial_offset_days' => 0
            ]
        ], $result);
    }

    /**
     * @test
     */
    public function it_is_restricted_by_from_date_and_length()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-07-01';
        $toDate = '2026-08-05';
        $result = $this->getAvailability(1, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals([
            [
                'dates' => [
                    '2026-07-06',
                    '2026-07-13',
                    '2026-07-20',
                    '2026-07-27'
                ],
                'embarkation_time' => [
                    'time' => '08:00:00',
                    'precision_taxonomy_id' => 339,
                    'time_of_day_taxonomy_id' => null
                ],
                'id' => 1,
                'technical_length_days' => 4,
                'financial_length_days' => 4,
                'financial_offset_days' => 0
            ]
        ], $result);
    }

    /**
     * @test
     */
    public function it_can_find_partial_matches_in_the_middle()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-07-07';
        $toDate = '2026-07-08';
        $result = $this->getAvailability(1, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals([
            [
                'dates' => [
                    '2026-07-07'
                ],
                'embarkation_time' => [
                    'time' => null,
                    'precision_taxonomy_id' => 337,
                    'time_of_day_taxonomy_id' => 341
                ],
                'id' => 1,
                'technical_length_days' => 2,
                'financial_length_days' => 3,
                'financial_offset_days' => -1
            ]
        ], $result);
    }

    /**
     * @test
     */
    public function it_can_find_partial_matches_in_the_beginning()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-07-06';
        $toDate = '2026-07-08';
        $result = $this->getAvailability(1, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals([
            [
                'dates' => [
                    '2026-07-06'
                ],
                'embarkation_time' => [
                    'time' => '08:00:00',
                    'precision_taxonomy_id' => 339,
                    'time_of_day_taxonomy_id' => null
                ],
                'id' => 1,
                'technical_length_days' => 3,
                'financial_length_days' => 3,
                'financial_offset_days' => 0
            ]
        ], $result);
    }

    /**
     * @test
     */
    public function it_can_find_partial_matches_in_the_end()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-07-07';
        $toDate = '2026-07-10';
        $result = $this->getAvailability(1, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals([
            [
                'dates' => [
                    '2026-07-07'
                ],
                'embarkation_time' => [
                    'time' => null,
                    'precision_taxonomy_id' => 337,
                    'time_of_day_taxonomy_id' => 341
                ],
                'id' => 1,
                'technical_length_days' => 2,
                'financial_length_days' => 3,
                'financial_offset_days' => -1
            ]
        ], $result);
    }

    /**
     * @test
     */
    public function it_can_find_the_best_partial_match_of_two_start_days()
    {
        $usageJson = '[{"usage": [{"age":21, "amount":2}]}]';
        $fromDate = '2026-07-09';
        $toDate = '2026-07-15';
        $result = $this->getAvailability(1, 1, $usageJson, $fromDate, $toDate);
        $this->assertEquals([
            [
                'dates' => [
                    '2026-07-13'
                ],
                'embarkation_time' => [
                    'time' => '08:00:00',
                    'precision_taxonomy_id' => 339,
                    'time_of_day_taxonomy_id' => null
                ],
                'id' => 1,
                'technical_length_days' => 3,
                'financial_length_days' => 3,
                'financial_offset_days' => 0
            ]
        ], $result);
    }

}
