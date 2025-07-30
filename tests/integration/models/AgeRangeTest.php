<?php
namespace Tests\Integration\Models;

use App\AgeRange;
use App\Organization;
use Tests\TestCase;

class AgeRangeTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function getAgeRangesInIntervalStaticCanReturnWithOverallInterval()
    {
        $this->assertNotEmpty(AgeRange::getAgeRangesInInterval(Organization::class, 1, 4, 5));
    }

    /**
     * @test
     */
    function getAgeRangesInIntervalStaticCanReturnWithOverallInfiniteInterval()
    {
        $this->assertNotEmpty(AgeRange::getAgeRangesInInterval(Organization::class, 1, 8, 9));
    }

    /**
     * @test
     */
    function getAgeRangesInIntervalStaticCanReturnWithStartAgeOverallInterval()
    {
        $this->assertNotEmpty(AgeRange::getAgeRangesInInterval(Organization::class, 1, 1, 4));
    }

    /**
     * @test
     */
    function getAgeRangesInIntervalStaticCanReturnWithEndAgeOverallInterval()
    {
        $this->assertNotEmpty(AgeRange::getAgeRangesInInterval(Organization::class, 1, -1, 4));
    }

    /**
     * @test
     */
    function getAgeRangesInIntervalStaticCanReturnWithToAgeCouldBeEqualOtherRangesFromAge()
    {
        $this->assertEmpty(AgeRange::getAgeRangesInInterval(Organization::class, 3, 0, 3));
    }
}
