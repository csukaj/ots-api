<?php

namespace Tests\Procedure\Ots\Search;

use App\Organization;
use Tests\Procedure\ProcedureTestCase;

class MarginCalculationFuncTest extends ProcedureTestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.room_search import RoomSearch' . PHP_EOL .
    'from ots.common.config import Config' . PHP_EOL;

    private $sampleDir = __DIR__ . '/MarginCalculationFuncTestData/';
    private $organizationId = 1;
    private $today;
    private $device;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function prepare($organizationId = null)
    {

        $this->device = Organization::findOrFail(($organizationId) ? $organizationId : 1)->devices[0];
        $this->today = date('Y-m-d');
    }

    private function prepareRoomSearch(
        $organizationId = 1,
        $request = [],
        $fromDate = null,
        $toDate = null,
        $bookingDate = null,
        $weddingDate = null,
        $displayMargin = false
    ) {
        $roomSearchConfigData = [
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $organizationId,
            'params' => json_encode([
                'request' => $request,
                'interval' => [
                    'date_from' => $fromDate,
                    'date_to' => $toDate
                ],
                'booking_date' => $bookingDate,
                'wedding_date' => $weddingDate
            ])
        ];
        if (!empty($displayMargin)) {
            $params = json_decode($roomSearchConfigData['params'], true);
            $params['display_margin'] = true;
            $roomSearchConfigData['params'] = json_encode($params);
        }
        $roomSearchConfig = $this->composeKeywordArguments($roomSearchConfigData);
        return self::$imports . PHP_EOL . "room_search = RoomSearch({$roomSearchConfig})" . PHP_EOL;
    }

    private function assertEqualsWithSample($sampleFilename, $actual)
    {
        $this->assertEqualsJSONFile($this->sampleDir . $sampleFilename, $actual);
    }

    private function getRooms(
        $organizationId,
        $request,
        $fromDate,
        $toDate,
        $bookingDate,
        $weddingDate = null,
        $displayMargins = false
    ) {
        $this->prepare($organizationId);
        $script = $this->prepareRoomSearch($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate,
            $displayMargins);
        $script .= "print room_search.get_rooms()" . PHP_EOL;
        return $this->jsonDecode($this->runPythonScript($script), true)['results'][0][0]['prices'];
    }

    /**
     * @test
     */
    function margin_is_not_displayed_by_default()
    {
        $actual = $this->getRooms(
            $this->organizationId,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2026-06-03',
            '2026-06-09',
            $this->today
        );
        foreach ($actual as $expectedPrice) {
            $this->assertArrayNotHasKey('margin', $expectedPrice);
        }
    }

    /**
     * @test
     */
    function it_can_calculate_margin_for_normal_price()
    {
        $actual = $this->getRooms(
            $this->organizationId,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2026-06-03',
            '2026-06-09',
            $this->today,
            null,
            true
        );
        $this->assertEqualsWithSample('it_can_calculate_margin_for_normal_price.json', $actual);
    }

    /**
     * @test
     */
    function it_can_calculate_margin_with_switch()
    {
        $actual = $this->getRooms(
            $this->organizationId,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2027-09-03',
            '2027-09-13',
            $this->today,
            null,
            true
        );
        $this->assertEqualsWithSample('it_can_calculate_margin_with_switch.json', $actual);
    }

    /**
     * @test
     */
    function it_can_calculate_margin_with_discounts()
    {
        $actual = $this->getRooms(
            $this->organizationId,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2027-06-03',
            '2027-06-09',
            $this->today,
            null,
            true
        );
        $this->assertEqualsWithSample('it_can_calculate_margin_with_discounts.json', $actual);
    }

    /**
     * @test
     */
    function it_can_calculate_margin_with_rule()
    {
        $actual = $this->getRooms(
            $this->organizationId,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2027-10-03',
            '2027-10-09',
            $this->today,
            null,
            true
        );
        $this->assertEqualsWithSample('it_can_calculate_margin_with_rule.json', $actual);
    }

    /**
     * @test
     */
    function it_can_calculate_margin_with_switch_and_discounts()
    {
        $actual = $this->getRooms(
            $this->organizationId,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2027-09-03',
            '2027-09-13',
            $this->today,
            '2026-09-01',
            true
        );
        $this->assertEqualsWithSample('it_can_calculate_margin_with_switch_and_multiple_discounts.json', $actual);
    }

}
