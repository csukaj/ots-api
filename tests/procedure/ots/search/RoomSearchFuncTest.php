<?php

namespace Tests\Procedure\Ots\Search;

use App\Organization;
use Illuminate\Support\Facades\Config;
use Tests\Procedure\ProcedureTestCase;

class RoomSearchFuncTest extends ProcedureTestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.room_search import RoomSearch' . PHP_EOL .
    'from ots.common.config import Config' . PHP_EOL;

    private $organization;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function prepare($organizationId = 1)
    {

        $this->organization = Organization::findOrFail($organizationId);
        $this->device = $this->organization->devices[0];
        $this->today = date('Y-m-d');
    }

    private function prepareRoomSearch(
        $organizationId = 1,
        $request = [],
        $fromDate = null,
        $toDate = null,
        $bookingDate = null,
        $weddingDate = null
    ) {
        $roomSearchConfig = $this->composeKeywordArguments([
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
        ]);
        return self::$imports . PHP_EOL . "room_search = RoomSearch({$roomSearchConfig})" . PHP_EOL;
    }

    private function runAndDecode($script)
    {
        //echo $this->runPythonScript($script);
        return $this->jsonDecode($this->runPythonScript($script), true);
    }

    private function assertEqualsWithSample($sampleFilename, $actual)
    {
        $this->assertEqualsJSONFile(__DIR__ . '/RoomSearchFuncTestData/' . $sampleFilename, $actual);
    }

    private function getRooms($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate = null)
    {
        $script = $this->prepareRoomSearch($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate);
        $script .= "print room_search.get_rooms()" . PHP_EOL;
        return $this->runAndDecode($script);
    }

    private function getDateRanges($organizationId, $fromTime, $toTime, $typeTxId)
    {
        $config = $this->composeParams(['App\\\\Organization', $organizationId, $fromTime, $toTime, $typeTxId]);
        $script = $this->prepareRoomSearch() . "print room_search._get_date_ranges({$config})" . PHP_EOL;
        return $this->runAndDecode($script);
    }

    private function loadPriceModifiers(
        $organizationId,
        $request,
        $fromDate,
        $toDate,
        $bookingDate,
        $weddingDate = null
    ) {
        $script = $this->prepareRoomSearch($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate);
        $script .= <<<"EOF"
room_search.date_ranges = room_search._get_open_and_price_modifier_date_ranges('App\\Organization', room_search.organization_id)
room_search.price_modifiers = room_search._get_price_modifiers(
    'App\\Organization',
    room_search.organization_id,
    room_search.date_ranges,
    room_search.available_devices,
    room_search.request_handler.age_resolver
 )
price_modifiers = []
for price_modifier in room_search.price_modifiers[Config.PRICE_MODIFIER_TYPE_DISCOUNT]:
    price_modifiers.append(price_modifier.properties)
print price_modifiers
EOF;
        return $this->runAndDecode($script);
    }

    /**
     * @test
     */
    function it_can_get_rooms()
    {
        $this->prepare();
        $actual = $this->getRooms(
            $this->organization->id,
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
            null
        );
        $this->assertEqualsWithSample('it_can_get_rooms.json', $actual);
    }

    /**
     * @test
     */
    function it_can_search_for_family_offer()
    {
        $this->prepare(2);
        $actual = $this->getRooms(
            $this->organization->id,
            [
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
            '2027-06-03',
            '2027-06-09',
            $this->today,
            null
        );
        $this->assertEqualsWithSample('it_can_search_for_family_offer.json', $actual);
    }

    /**
     * @test
     */
    function it_can_get_open_date_ranges()
    {
        $this->prepare();
        $actual = $this->getDateRanges(
            $this->organization->id, '2027-06-03 0:00:00', '2027-06-13 23:59:59',
            Config::get('taxonomies.date_range_types.open')
        );
        $this->assertEqualsWithSample('it_can_get_open_date_ranges.json', $actual);
    }

    /**
     * @test
     */
    function it_can_get_price_modifier_date_ranges()
    {
        $this->prepare();
        $result = $this->getDateRanges(
            $this->organization->id, '2027-06-03 0:00:00', '2027-06-13 23:59:59',
            Config::get('taxonomies.date_range_types.price_modifier')
        );

        $expected = [
            [
                'margin_type_taxonomy_id' => 57,
                'minimum_nights' => null,
                'name_description_id' => null,
                'price_modifier_ids' => [1, 2, 3, 4, 5, 6, 10],
                'from_time' => '2027-06-01 00:00:00',
                'type_taxonomy_id' => 164,
                'to_time' => '2027-09-01 23:59:59',
                'margin_value' => null,
            ]
        ];

        foreach ($expected as $index => $expectedRow) {
            foreach ($expectedRow as $key => $value) {
                if ($key == 'price_modifier_ids') {
                    continue;
                }
                $this->assertEquals($value, $result[$index][$key]);
            }
        }

        foreach ($expected as $index => $expectedRow) {
            $this->assertEquals(count($expectedRow['price_modifier_ids']),
                count($result[$index]['price_modifier_ids']));
        }
    }

    /**
     * @test
     */
    public function it_can_load_price_modifiers()
    {
        $this->prepare();
        $actual = $this->loadPriceModifiers(
            $this->organization->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2],
                        ['age' => 1, 'amount' => 1]
                    ]
                ],
                [
                    'usage' => [
                        ['age' => 8, 'amount' => 1],
                        ['age' => 11, 'amount' => 1]
                    ]
                ]
            ],
            '2027-06-03',
            '2027-06-13',
            $this->today,
            null
        );
        $this->assertEqualsWithSample('it_can_load_discounts.json', $actual);
    }

    /**
     * @test
     */
    public function it_can_search_price_from_last_prices()
    {
        $this->prepare();
        $actual = $this->getRooms(
            $this->organization->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            '2028-09-02',
            '2028-09-08',
            $this->today,
            null
        );
        $this->assertEqualsWithSample('it_can_search_price_from_last_prices.json', $actual);

    }

    /**
     * @test
     */
    function result_for_1_a_1_c_1_r_from_26_1_20_26()
    {
        $this->prepare();
        $actual = $this->getRooms(
            $this->organization->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1],
                        ['age' => 2, 'amount' => 1]
                    ]
                ]
            ],
            '2026-01-20',
            '2026-01-26',
            $this->today,
            '2027-07-21'
        );
        $this->assertEqualsWithSample('result_for_1_a_1_c_1_r_from_26_1_20_26.json', $actual);
    }


    /**
     * @test
     */
    function daterange_missing_hotelf()
    {
        $this->prepare(6);
        $resultStr = $this->getRooms(
            $this->organization->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            "2026-07-28",
            "2026-08-03",
            "2016-12-21"
        );
        $result = \json_decode($resultStr, true);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    function it_respects_strict_child_room_policy()
    {
        $this->prepare(13);
        $actual = $this->getRooms(
            $this->organization->id,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1],
                        ['age' => 7, 'amount' => 1],
                        ['age' => 8, 'amount' => 1]
                    ]
                ]
            ],
            '2027-07-20',
            '2027-07-26',
            $this->today
        );
        $this->assertEqualsWithSample('it_respects_strict_child_room_policy.json', $actual);
    }


}
