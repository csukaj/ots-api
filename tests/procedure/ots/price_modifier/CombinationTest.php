<?php

namespace Tests\Procedure\Ots\PriceModifier;

use Tests\Procedure\ProcedureTestCase;

class CombinationTest extends ProcedureTestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    static private $imports = 'from ots.search.room_search import RoomSearch' . PHP_EOL .
    'from ots.common.config import Config' . PHP_EOL;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    private function getSearchResult($organizationId, $request, $fromDate, $toDate, $bookingDate, $weddingDate = null)
    {
        $roomSearchConfig = $this->composeKeywordArguments([
            'plpy' => $this->scriptContainer('plpy_mocker'),
            'organization_id' => $organizationId,
            'params' => json_encode([
                'request' => $request,
                'interval' => [
                    'date_from' => $fromDate ?: $this->dateFrom,
                    'date_to' => $toDate ?: $this->dateTo
                ],
                'booking_date' => $bookingDate,
                'wedding_date' => $weddingDate
            ])
        ]);
        $script = self::$imports . PHP_EOL . "room_search = RoomSearch({$roomSearchConfig})" . PHP_EOL;
        $script .= "print room_search.get_rooms()" . PHP_EOL;
        return $this->runPythonScript($script);
        return $this->jsonDecode($this->runPythonScript($script), true);
    }


    /**
     * @test
     */
    function it_can_calculate_when_discount_ranges_overlaps_and_discounts_not_combinable()
    {
        // because of this free night cannot be added here
        $this->markTestSkipped("Currently, we check free night combinability only it's date range");

        $actual = $this->getSearchResult(
            11,
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2]
                    ]
                ]
            ],
            '2027-03-25',
            '2027-05-10',
            date('Y-m-d'),
            '2027-03-15'
        );

        $expected =
            [
                'original_price' => '72399.8',
                'order_itemable_index' => 0,
                'discounted_price' => '51664.14',
                'meal_plan' => 'h/b',
                'discounts' =>
                    [
                        [
                            'name' => ['en' => 'Bonus Nights offer'],
                            'offer' => 'free_nights',
                            'discount_percentage' => -3.0099999999999998,
                            'discount_value' => -2175.8000000000002,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'description' => null,
                        ],
                        [
                            'name' => ['en' => 'Honeymoon Offer'],
                            'offer' => 'percentage',
                            'discount_percentage' => -18.420000000000002,
                            'discount_value' => -13337.939999999997,
                            'modifier_type' => 491,
                            'condition' => 'anniversary',
                            'description' => null,
                        ],
                        [
                            'name' => ['en' => 'Honeymoon Offer'],
                            'offer' => 'percentage',
                            'discount_percentage' => -7.21,
                            'discount_value' => -5221.920000000001,
                            'modifier_type' => 491,
                            'condition' => 'anniversary',
                            'description' => null,
                        ],
                    ],
                'meal_plan_id' => 3,
                'total_discount' => ['percentage' => -28.64, 'value' => -20735.66],


            ];

        $actual = json_decode($actual, true);
        $this->assertEquals($expected, $actual['results'][0][0]['prices'][0]);
    }


}
