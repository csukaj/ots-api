<?php

namespace Tests\Functional\Controllers;

use App\Entities\Search\AccommodationSearchEntity;
use App\Island;
use DirectoryIterator;
use Tests\TestCase;

class AccommodationSearchControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    protected $accommodationJsonData;
    static protected $accommodationPairs = [];
    protected $_url = '/accommodation-search';
    protected $token = []; // Token is empty for normal search

    public function setUp(): void
    {
        parent::setUp();
        $this->accommodationJsonData = $this->getAccommodationJsonData();
    }

    private function getAccommodationJsonData()
    {
        $directory = __DIR__ . '/../../../docs/accommodations/';
        $directoryIterator = new DirectoryIterator($directory);
        $data = [];

        foreach ($directoryIterator as $fileInfo) {
            if ((!$fileInfo->isDot())) {
                $data[] = json_decode(file_get_contents($fileInfo->getPathname()));
            }
        }
        return $data;
    }

    /**
     * @test
     */
    public function it_can_list_accommodations()
    {
        $this->assertSuccessfulHttpApiRequest($this->_url, 'POST', $this->token);
    }

    /**
     * @test
     */
    public function it_has_correct_organization_types()
    {
        $responseData = $this->assertSuccessfulHttpApiRequest($this->_url, 'POST', $this->token);
        foreach ($responseData->data as $row) {
            $this->assertEquals('accommodation', $row->info->type);
        }
    }

    /**
     * @test
     */
    public function it_has_all_migrated_accommodations()
    {
        static::$accommodationPairs = [];
        $responseData = $this->assertSuccessfulHttpApiRequest(
            $this->_url,
            'POST',
            $this->token,
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

        foreach ($this->accommodationJsonData as $inputAccommodation) {
            $found = false;
            foreach ($responseData->data as $row) {
                if ($inputAccommodation->id == $row->info->id) {
                    $found = true;
                    static::$accommodationPairs[] = ['input' => $inputAccommodation, 'output' => $row->info];
                    continue;
                }
            }
            $this->assertTrue($found);
        }
    }

    /**
     * @test
     */
    public function pairs_have_matching_accommodation_names()
    {
        foreach (static::$accommodationPairs as $accommodationPair) {
            foreach ($accommodationPair['input']->name as $languageCode => $translation) {
                $this->assertEquals($translation, $accommodationPair['output']->name->$languageCode);
            }
        }
    }

    /**
     * @test
     */
    public function pairs_have_matching_base_datas()
    {
        foreach (static::$accommodationPairs as $accommodationPair) {
            $this->assertEquals($accommodationPair['input']->id, $accommodationPair['output']->id);
            $this->assertEquals($accommodationPair['input']->type, $accommodationPair['output']->type);
            $this->assertEquals($accommodationPair['input']->is_active, $accommodationPair['output']->is_active);
        }
    }

    /**
     * @test
     */
    public function pairs_have_matching_islands()
    {
        foreach (static::$accommodationPairs as $accommodationPair) {
            $this->assertEquals($accommodationPair['input']->location->island,
                $accommodationPair['output']->location->island);
        }
    }

    /**
     * @test
     * @throws \App\Exceptions\UserException
     * @throws \Exception
     */
    public function it_can_be_filtered_by_usage()
    {
        $responseData = $this->assertSuccessfulHttpApiRequest(
            $this->_url, 'POST', $this->token,
            ['usages' => [['usage' => [['age' => 21, 'amount' => 4]]]], 'interval' => []]
        );

        $this->assertEqualStructures(
            (new AccommodationSearchEntity())->setParameters([
                'usages' => [['usage' => [['age' => 21, 'amount' => 4]]]],
                'interval' => []
            ])->getFrontendData(['frontend']),
            $responseData->data
        );
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_island()
    {
        $request = [
            'islands' => [Island::findByName('Mahé')->id],
            'usages' => [['usage' => [['age' => 21, 'amount' => 4]]]],
            'interval' => []
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest($this->_url, 'POST', $this->token, $request);

        $this->assertEqualStructures(
            (new AccommodationSearchEntity())->setParameters($request)->getFrontendData(['frontend']),
            $responseData->data
        );
    }

    /**
     * @test
     */
    public function it_can_be_filtered_by_accommodations()
    {
        $request = [
            'organizations' => [1, 2],
            'usages' => [['usage' => [['age' => 21, 'amount' => 4]]]],
            'interval' => []
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest($this->_url, 'POST', $this->token, $request, true);

        $this->assertEquals(
            (new AccommodationSearchEntity())->setParameters($request)->getFrontendData(['frontend']),
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
        $responseData = $this->assertSuccessfulHttpApiRequest($this->_url, 'POST', $this->token, $request, true);
        $this->assertTrue($responseData['success']);
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
        $responseData = $this->assertSuccessfulHttpApiRequest($this->_url, 'POST', $this->token, $request, true);
        $this->assertTrue($responseData['success']);
    }

}
