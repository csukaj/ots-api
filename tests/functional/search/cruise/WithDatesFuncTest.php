<?php

namespace Tests\Functional\Search\Cruise;

use App\Entities\Search\CruiseSearchEntity;
use Tests\TestCase;

class WithDatesFuncTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    
    /**
     * @test
     */
    function it_can_be_queried_for_intervals() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $actual = $cruiseSearchEntity->setParameters([
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
        ])->getFrontendData();
        $this->assertNotEmpty($actual);
    }

    /**
     * @test
     */
    function it_can_be_queried_for_interval_with_2_room_request() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $actual = $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2026-06-30',
                'date_to' => '2026-07-05'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ],
                [
                    'usage' => [
                            ['age' => 21, 'amount' => 2]
                    ]
                ]
            ]
        ])->getFrontendData();
        $this->assertNotEmpty($actual);
    }

    /**
     * @test
     */
    function it_respects_closed_date_range_at_interval_ending() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendData = $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2026-07-10',
                'date_to' => '2026-07-17'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            'organizations' => [1]
        ])->getFrontendData();
        $this->assertEmpty($frontendData);
    }

    /**
     * @test
     */
    function it_respects_closed_date_range_covering_an_interval() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendData = $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2026-07-12',
                'date_to' => '2026-07-15'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            'organizations' => [1]
        ])->getFrontendData();

        $this->assertEmpty($frontendData);
    }

    /**
     * @test
     */
    function it_respects_closed_date_range_inside_an_interval() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendData = $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2026-07-09',
                'date_to' => '2026-08-03'
            ],
            'usages' => [
                [
                    'usage' => [
                            ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            'organizations' => [1]
        ])->getFrontendData();
        $this->assertEmpty($frontendData);
    }

    /**
     * @test
     */
    function it_respects_closed_date_range_at_interval_beginning() {
        $cruiseSearchEntity = new CruiseSearchEntity();
        $frontendData = $cruiseSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2026-07-30',
                'date_to' => '2026-08-04'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ],
            'organizations' => [1]
        ])->getFrontendData();
        $this->assertEmpty($frontendData);
    }
}
