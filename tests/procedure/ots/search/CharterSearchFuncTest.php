<?php

namespace Tests\Procedure\Ots\Search;

use App\Organization;
use App\OrganizationGroup;
use Tests\Procedure\ProcedureTestCase;

class CharterSearchFuncTest extends ProcedureTestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.charter_search import CharterSearch' . PHP_EOL;

    private $organization;
    private $organizationGroup;
    private $today;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function prepare($organizationId = null, $organizationGroupId = null)
    {
        $this->organization = Organization::findOrFail($organizationId ?: 301);
        $this->organizationGroup = OrganizationGroup::findOrFail($organizationGroupId ?: 1);
        $this->today = date('Y-m-d');
    }

    private function prepareCharterSearch(
        $organizationId = 301,
        $organizationGroupId = 1,
        $request = [],
        $fromDate = null,
        $toDate = null,
        $bookingDate = null,
        $weddingDate = null
    ) {
        $charterSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $organizationId,
            'organization_group_id' => $organizationGroupId,
            'params' => json_encode([
                'request' => $request,
                'interval' => [
                    'date_from' => $fromDate,
                    'date_to' => $toDate
                ],
                'booking_date' => $bookingDate,
                'wedding_date' => $weddingDate
            ])
        ]);
        return self::$imports . PHP_EOL . "charter_search = CharterSearch({$charterSearchConfig})" . PHP_EOL;
    }

    private function assertEqualsWithSample($sampleFilename, $actual)
    {
        $this->assertEqualsJSONFile(__DIR__ . '/CharterSearchFuncTestData/' . $sampleFilename, $actual);
    }

    private function getCharters(
        $organizationId,
        $organizationGroupId,
        $request,
        $fromDate,
        $toDate,
        $bookingDate,
        $weddingDate = null
    ) {
        $script = $this->prepareCharterSearch($organizationId, $organizationGroupId, $request, $fromDate, $toDate,
            $bookingDate, $weddingDate);
        $script .= 'print charter_search.get_charters()' . PHP_EOL;
        return $this->jsonDecode($this->runPythonScript($script), true);
    }

    /**
     * @test
     */
    function it_can_get_a_charter()
    {
        $this->prepare();
        $actual = $this->getCharters(
            $this->organization->id,
            $this->organizationGroup->id,
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
            null
        );
        $this->assertEqualsWithSample('it_can_get_a_charter.json', $actual);
    }

    /**
     * @test
     */
    function it_can_search_for_free_nights_offer()
    {
        $this->prepare();
        $actual = $this->getCharters(
            $this->organization->id,
            $this->organizationGroup->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2]
                    ]
                ]
            ],
            '2027-06-03',
            '2027-06-09',
            $this->today,
            null
        );
        $this->assertEqualsWithSample('it_can_search_for_free_nights_offer.json', $actual);
    }

    /**
     * @test
     */
    function it_can_search_for_tiered_price_offer()
    {
        $this->markTestIncomplete('Need to pass cart to calculate group discount');
        $this->prepare();
        $actual = $this->getCharters(
            $this->organization->id,
            $this->organizationGroup->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 15]
                    ]
                ]
            ],
            '2027-08-20',
            '2027-08-29',
            $this->today,
            null
        );
        $this->assertEqualsWithSample('it_can_search_for_tiered_price_offer.json', $actual);
    }

}