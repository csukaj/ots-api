<?php

namespace Tests\Functional\Search\Accommodation;

use App\Entities\Search\AccommodationSearchEntity;
use Tests\TestCase;

class WithDatesFuncTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;
    
    /**
     * @test
     */
    function it_can_be_queried_for_intervals() {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $actual = $accommodationSearchEntity->setParameters([
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $actual = $accommodationSearchEntity->setParameters([
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendData = $accommodationSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2026-07-08',
                'date_to' => '2026-07-13'
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendData = $accommodationSearchEntity->setParameters([
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendData = $accommodationSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2026-07-01',
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
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendData = $accommodationSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2026-07-30',
                'date_to' => '2026-08-10'
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
