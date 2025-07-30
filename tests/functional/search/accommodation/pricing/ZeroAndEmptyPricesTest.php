<?php

namespace Tests\Functional\Search\Accommodation\Pricing;

use App\AgeRange;
use Tests\TestCase;

class ZeroAndEmptyPricesTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepareAndRun($fromDate, $toDate, $accommodation='Hotel A', $usage=[['usage' => [['age' => 21, 'amount' => 1],['age' => 1, 'amount' => 1]]]]) {
        return $this->prepareAccommodationSearchResult(
                ['date_from' => $fromDate, 'date_to' => $toDate], 
                $accommodation,
                $usage
                );
    }

    /**
     * @test
     */
    public function it_works_with_zero_price() {
        $actual = $this->prepareAndRun('2026-06-10', '2026-06-20')['results'][0][1]['prices'][1];
        $this->assertEquals(1210, $actual['original_price']);
    }

    /**
     * @test
     */
    public function it_works_with_empty_price() {
        $actual = $this->prepareAndRun('2026-09-10', '2026-09-20')['results'][0][1]['prices'][1];
        $this->assertEquals(2750, $actual['original_price']);
    }

    /**
     * @test
     */
    public function it_ignores_free_age_range_in_price_calculation()
    {
        //disable free to test normal case
        AgeRange::where('from_age',16)->where('age_rangeable_id',15)->update(['free'=>false]);
        $actual = $this->prepareAndRun('2026-06-01', '2026-06-06', 'Hotel I', [['usage' => [['age' => 21, 'amount' => 1],['age' => 16, 'amount' => 1]]]])['results'][0][0]['prices'][0];
        $this->assertEquals(550, $actual['original_price']);

        //set back to free
        AgeRange::where('from_age',16)->where('age_rangeable_id',15)->update(['free'=>true]);
        $actual = $this->prepareAndRun('2026-06-01', '2026-06-06', 'Hotel I', [['usage' => [['age' => 21, 'amount' => 1],['age' => 16, 'amount' => 1]]]])['results'][0][0]['prices'][0];
        $this->assertEquals(330, $actual['original_price']);

    }
}
