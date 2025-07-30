<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use App\Entities\CartElementEntity;
use App\Entities\CartEntity;
use Tests\TestCase;

class MaximumNightsOnTypesTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare(
        $interval, $accommodationName = 'Hotel A',
        $usage = [['usage' => [['age' => 21, 'amount' => 1]]]],
        $wedding = null
    ) {
        return $this->prepareAccommodationSearchResult($interval, $accommodationName, $usage, $wedding);   
    }

    private function checkApplicable(
        $accommodationName, $intervalPositive, $intervalNegative, $expectedDiscount,
        $usage = [['usage' => [['age' => 21, 'amount' => 1]]]],
        $wedding = null
    ) {
        $priceModifierFound = false;
        $results = $this->prepare($intervalPositive, $accommodationName, $usage, $wedding)['results'];
        foreach ($results as $room) {
            $priceModifiers = $room[0]['prices'][0]['discounts'];
            foreach ($priceModifiers as $priceModifier) {
                if ($priceModifier['name']['en'] == $expectedDiscount) {
                    $priceModifierFound = true;
                }
            }
        }
        $this->assertTrue($priceModifierFound);

        $priceModifierFound2 = false;
        $actual2 = $this->prepare($intervalNegative, $accommodationName, $usage)['results'][0][0]['prices'][0]['discounts'];
        foreach ($actual2 as $priceModifier) {
            if ($priceModifier['name']['en'] == $expectedDiscount) {
                $priceModifierFound2 = true;
            }
        }
        $this->assertFalse($priceModifierFound2);
    }

    /**
     * @test
     */
    public function maximum_nights_works_for_early_bird_fixed_date()
    {
        $this->checkApplicable(
            'Hotel Minimum Night',
            ['date_from' => '2027-03-01', 'date_to' => '2027-03-04'],
            ['date_from' => '2027-03-01', 'date_to' => '2027-03-08'],
            'Early bird fixed date w/ fixed price if minimum nights is 3'
        );
    }

    /**
     * @test
     */
    public function maximum_nights_works_for_early_bird()
    {
        $this->checkApplicable(
            'Hotel Minimum Night',
            ['date_from' => '2027-01-01', 'date_to' => '2027-01-04'],
            ['date_from' => '2027-01-01', 'date_to' => '2027-01-08'],
            'Early bird w/ fixed price if minimum nights is 3'
        );
    }

    /**
     * @test
     */
    public function maximum_nights_works_for_anniversary()
    {
        $this->checkApplicable(
            'Hotel Minimum Night',
            ['date_from' => '2027-03-16', 'date_to' => '2027-03-19'],
            ['date_from' => '2027-03-16', 'date_to' => '2027-03-22'],
            'Wedding Anniversary w/ fixed price if minimum nights is 3 and anniversary in 60 days range',
            [['usage' => [['age' => 21, 'amount' => 2]]]],
            '2026-03-16'
        );
    }

    /**
     * @test
     */
    public function maximum_nights_works_for_long_stay()
    {
        $this->checkApplicable(
            'Hotel Minimum Night',
            ['date_from' => '2027-02-01', 'date_to' => '2027-02-04'],
            ['date_from' => '2027-02-01', 'date_to' => '2027-02-08'],
            'Long stay w/ fixed price if minimum nights is 3'
        );
    }

    /**
     * @test
     */
    public function maximum_nights_works_for_room_sharing()
    {
        $this->checkApplicable(
            'Hotel Minimum Night',
            ['date_from' => '2027-01-16', 'date_to' => '2027-01-19'],
            ['date_from' => '2027-01-16', 'date_to' => '2027-01-22'],
            'Family room sharing w/ fixed price if minimum nights is 3',
            [['usage' => [
                ['age' => 21, 'amount' => 2],
                ['age' => 8, 'amount' => 1]
            ]
            ]]
        );
    }

    /**
     * @test
     */
    public function maximum_nights_works_for_maximum_nights()
    {
        $this->checkApplicable(
            'Hotel Minimum Night',
            ['date_from' => '2027-02-16', 'date_to' => '2027-02-19'],
            ['date_from' => '2027-02-16', 'date_to' => '2027-02-22'],
            'Minimum nights w/ fixed price if minimum nights is 3'
        );
    }

    /**
     * @test
     */
    public function maximum_nights_works_for_child_room()
    {
        $this->checkApplicable(
            'Hotel Minimum Night',
            ['date_from' => '2027-04-01', 'date_to' => '2027-04-04'],
            ['date_from' => '2027-04-01', 'date_to' => '2027-04-08'],
            'Child room discount w/ fixed price if minimum nights is 3',
            [
                [
                    'usage' => [['age' => 21, 'amount' => 2]]
                ],
                [
                    'usage' => [['age' => 8, 'amount' => 1]]
                ]
            ]
        );
    }

}
