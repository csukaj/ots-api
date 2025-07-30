<?php

namespace Tests\Functional\Search\Accommodation;

use App\Entities\Search\AccommodationSearchEntity;
use App\Island;
use App\MealPlan;
use Tests\TestCase;

class FiltersFuncTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_be_queried_by_id()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $this->assertEqualArrayContents(
            [1], array_keys($accommodationSearchEntity->setParameters([
                'organizations' => [1],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ]
                ]
            ])->getFrontendData())
        );
    }

    /**
     * @test
     */
    function it_can_be_queried_by_ids()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $this->assertEqualArrayContents(
            [1, 2], array_keys($accommodationSearchEntity->setParameters([
                'organizations' => [1, 2],
                'usages' => [
                    [
                        'usage' => [
                            ['age' => 21, 'amount' => 1]
                        ]
                    ]
                ]
            ])->getFrontendData())
        );
    }
}
