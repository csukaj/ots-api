<?php

namespace Tests\Functional\Search\Accommodation;

use App\Entities\Search\AccommodationSearchEntity;
use App\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchSpeedTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function speedTest($parameters, $withInfo=true)
    {
        $startTime = microtime(true);
        if($withInfo) {
            $results = (new AccommodationSearchEntity())->setParameters($parameters)->getFrontendData();
        }else{
            $results = $this->getAccommodationDataByParameters($parameters);
        }
        $endTime = microtime(true);
        //print count($results);
        return $endTime - $startTime;
    }

    private function getAccommodationDataByParameters($parameters)
    {
        if(!isset($parameters['booking_date'])){
            $parameters['booking_date'] = date('Y-m-d');
        }
        $parametersJSON = json_encode([
            'request' => $parameters['usages'],
            'interval' => $parameters['interval'],
            'booking_date' => $parameters['booking_date'],
            'wedding_date' => !empty($parameters['wedding_date']) ? $parameters['wedding_date'] : null,
            'cart_summary' => null,
            'returning_client' => !empty($parameters['returning_client'])
        ]);

        $query = DB::table('organizations AS o')->distinct();
        $query->where('o.type_taxonomy_id', Config::getOrFail('taxonomies.organization_types.accommodation.id'));
        $query->whereRaw('o.is_active');
        $query->whereNull('o.deleted_at');
        $query->selectRaw("o.id, get_result_rooms(o.id, TEXT '{$parametersJSON}') AS result_usages");

        return $query->get()->filter(function ($value, $key) {
            return !is_null($value->result_usages);
        })->values();
    }

    /**
     * @test
     */
    function it_can_measure_time_for_a_simple_search()
    {
        /*timing kezdetben:
        10.75-11.15 sec
        timing kezdetben infoval:
        21.0-21.5sec
        */


        $parameters = [
            'interval' => [
                'date_from' => '2026-06-30',
                'date_to' => '2026-07-05'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ]
        ];
        $elapsedTime = $this->speedTest($parameters);
        $this->assertLessThan(4,$elapsedTime);
    }

    /**
     * @test
     */
    function it_can_measure_time_for_an_average_search()
    {
        /*timing kezdetben:
        14.60-15.25 sec
        timing kezdetben infoval:
        26.0-26.5 sec
        */
        $parameters = [
            'interval' => [
                'date_from' => '2026-10-25',
                'date_to' => '2026-11-05'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2],
                        ['age' => 9, 'amount' => 1]

                    ],
                    'usage' => [
                    ['age' => 21, 'amount' => 2]
                ]
                ]
            ]
        ];
        $elapsedTime = $this->speedTest($parameters);
        $this->assertLessThan(4,$elapsedTime);
    }

    /**
     * @test
     */
    function it_can_measure_time_for_a_complicated_search_in_a_complicated_period_with_many_results()
    {
        /*timing kezdetben csak python:
        77.50-81.5 sec
        timing kezdetben infoval:
        ~ 97 sec
        */

        $parameters = [
            'interval' => [
                'date_from' => '2019-12-13',
                'date_to' => '2020-01-13'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 2]
                    ]
                ],
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            'wedding_date' => '2013-12-01'
        ];

        $elapsedTime = $this->speedTest($parameters);
        $this->assertLessThan(4,$elapsedTime);
    }

}
