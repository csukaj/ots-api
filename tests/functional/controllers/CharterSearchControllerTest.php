<?php

namespace Tests\Functional\Controllers;

use App\Entities\Search\CharterSearchEntity;
use DirectoryIterator;
use Tests\TestCase;

class CharterSearchControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    private $shipGroupsJsonData;
    static private $charterPairs = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->shipGroupsJsonData = $this->getShipGroupsJsonData();
    }

    private function getShipGroupsJsonData()
    {
        $directory = __DIR__ . '/../../../docs/ship_groups/';
        $directoryIterator = new DirectoryIterator($directory);
        $data = [];

        foreach ($directoryIterator as $fileInfo) {
            if (!$fileInfo->isDot()) {
                $data[] = json_decode(file_get_contents($fileInfo->getPathname()));
            }
        }

        return $data;
    }

    /**
     * @test
     */
    public function it_can_list_charters()
    {
        $this->assertSuccessfulHttpApiRequest('/charter-search', 'POST');
    }

    /**
     * @test
     */
    public function it_has_correct_organization_group_types()
    {
        $responseData = $this->assertSuccessfulHttpApiRequest('/charter-search', 'POST');
        foreach ($responseData->data as $row) {
            $this->assertEquals('ship_group', $row->info->type);
        }
    }

    /**
     * @test
     */
    public function it_has_all_migrated_charters()
    {
        static::$charterPairs = [];
        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/charter-search',
            'POST',
            null,
            [
                'interval' => [],
                'islands' => [],
                'meal_plans' => [],
                'organizations' => [],
                'usages' => [
                    [
                        'usage' => [
                            [
                                'age' => 21,
                                'amount' => 1
                            ]
                        ]
                    ]
                ]
            ]
        );

        foreach ($this->shipGroupsJsonData as $inputCharter) {
            $found = false;
            foreach ($responseData->data as $row) {
                if ($inputCharter->id == $row->info->id) {
                    $found = true;
                    static::$charterPairs[] = ['input' => $inputCharter, 'output' => $row->info];
                    continue;
                }
            }
            $this->assertTrue($found);
        }
    }

    /**
     * @test
     */
    public function pairs_have_matching_charter_names()
    {
        foreach (static::$charterPairs as $charterPair) {
            foreach ($charterPair['input']->name as $languageCode => $translation) {
                $this->assertEquals($translation, $charterPair['output']->name->$languageCode);
            }
        }
    }

    /**
     * @test
     */
    public function pairs_have_matching_base_datas()
    {
        foreach (static::$charterPairs as $charterPair) {
            $this->assertEquals($charterPair['input']->id, $charterPair['output']->id);
            $this->assertEquals($charterPair['input']->type, $charterPair['output']->type);
            $this->assertEquals($charterPair['input']->is_active, $charterPair['output']->is_active);
        }
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_usage()
    {
        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/charter-search', 'POST', [], ['usages' => [['usage' => [['age' => 21, 'amount' => 4]]]], 'interval' => []]
        );

        $this->assertEqualStructures(
            (new CharterSearchEntity())->setParameters([
                'usages' => [['usage' => [['age' => 21, 'amount' => 4]]]],
                'interval' => []
            ])->getFrontendData(['frontend']),
            $responseData->data
        );
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_charters()
    {
        $request = [
            'organizations' => [1, 2],
            'usages' => [['usage' => [['age' => 21, 'amount' => 4]]]],
            'interval' => []
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest('/charter-search', 'POST', [], $request, true);

        $this->assertEquals(
            (new CharterSearchEntity())->setParameters($request)->getFrontendData(['frontend']),
            $responseData['data']
        );
    }

    /**
     * @test
     */
    public function it_can_get_prices_with_price_modifiers()
    {
        $request = [
            'organizations' => [1],
            'usages' => [['usage' => [['age' => 21, 'amount' => 1]]]],
            'interval' => [
                'date_from' => '2027-07-03',
                'date_to' => '2027-07-09'
            ]
        ];
        $this->assertSuccessfulHttpApiRequest('/charter-search', 'POST', [], $request, true);
    }

    /**
     * @test
     */
    public function it_can_search_for_family_offer()
    {
        $request = [
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2]
                    ]
                ],
                [
                    'usage' => [
                        ['age' => 4, 'amount' => 1],
                        ['age' => 3, 'amount' => 1]
                    ]
                ]
            ],
            'interval' => [
                'date_from' => '2027-06-03',
                'date_to' => '2027-06-09'
            ]
        ];
        $this->assertSuccessfulHttpApiRequest('/charter-search', 'POST', [], $request, true);
    }

}
