<?php

namespace Tests\Procedure\Ots\Search;

use App\Cruise;
use App\Organization;
use Tests\Procedure\ProcedureTestCase;

class CruiseSearchFuncTest extends ProcedureTestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.cruise_search import CruiseSearch' . PHP_EOL;

    private $organization;
    private $cruise;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function prepare($organizationId = null, $cruiseId = null)
    {
        $this->organization = Organization::findOrFail($organizationId ? $organizationId : 1);
        $this->cruise = Cruise::findOrFail($cruiseId ? $cruiseId : 1);

        if (!isset($this->device)) {
            $this->device = $this->organization->devices[0];
        }
        $this->today = date('Y-m-d');
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
                    'interval'=>[
                        'date_from' => $fromDate,
                        'date_to' => $toDate
                    ],
                'booking_date' => $bookingDate,
                'wedding_date' => $weddingDate
            ])
        ]);
        return self::$imports . PHP_EOL . "cruise_search = CruiseSearch({$cruiseSearchConfig})" . PHP_EOL;
    }

    private function runAndDecode($script)
    {
        return $this->jsonDecode($this->runPythonScript($script), true);
    }

    private function assertEqualsWithSample($sampleFilename, $actual)
    {
        $this->assertEqualsJSONFile(__DIR__.'/CruiseSearchFuncTestData/' . $sampleFilename, $actual);
    }

    private function getCabins($organizationId, $cruiseId, $request, $fromDate, $toDate, $bookingDate, $weddingDate = null)
    {
        $script = $this->prepareCruiseSearch($organizationId, $cruiseId, $request, $fromDate, $toDate, $bookingDate, $weddingDate);
        $script .= 'print cruise_search.get_cabins()' . PHP_EOL;
        return $this->runAndDecode($script);
    }

    /**
     * @test
     */
    function it_can_get_cabins_with_prices_for_full_itinerary()
    {
        $this->prepare();
        $actual = $this->getCabins(
            $this->organization->id,
            $this->cruise->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2026-06-01',
            '2026-06-04',
            $this->today,
            null
        );
        $this->assertEqualsWithSample('it_can_get_cabins_with_prices_for_full_itinerary.json', $actual);
    }

    /**
     * @test
     */
    function it_can_get_cabins_with_prices_for_partial_itinerary()
    {
        $this->prepare();
        $actual = $this->getCabins(
            $this->organization->id,
            $this->cruise->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2026-06-01',
            '2026-06-03',
            $this->today,
            null
        );
        $this->assertEqualsWithSample('it_can_get_cabins_with_prices_for_partial_itinerary.json', $actual);
    }

}
