<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class SearchWithPartiallyValidDiscounts extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare(
        $interval, $accommodationName = 'Hotel J',
        $usage = [['usage' => [['age' => 21, 'amount' => 1]]]],
        $wedding = null
    ) {
        return $this->prepareAccommodationSearchResult($interval, $accommodationName, $usage, $wedding);   
    }

    private function checkDiscount(
        $accommodationName, $intervalPositive, $expectedDiscount,
        $expectedValue,
        $usage = [['usage' => [['age' => 21, 'amount' => 1]]]],
        $wedding = null
    ) {
        $priceModifierFound = false;
        $priceModifier = null;
        $results = $this->prepare($intervalPositive, $accommodationName, $usage, $wedding)['results'];
        foreach ($results as $room) {
            $actualDiscounts = $room[0]['prices'][0]['discounts'];
            foreach ($actualDiscounts as $actualDiscount) {
                if ($actualDiscount['name']['en'] == $expectedDiscount) {
                    $priceModifierFound = true;
                    $priceModifier = $actualDiscount;
                }
            }
        }
        $this->assertTrue($priceModifierFound);
        $this->assertEquals($expectedValue, $priceModifier['discount_value']);
    }

    /**
     * @test
     */
    public function works_only_in_validity_of_fixed_price_offer()
    {
        $this->checkDiscount(
            'Hotel J',
            ['date_from' => '2027-05-25', 'date_to' => '2027-06-05'],
            'Annual Minimum Nights',
            -100
        );
    }

    /**
     * @test
     */
    public function works_only_in_validity_of_free_nights_offer()
    {
        $this->checkDiscount(
            'Hotel A',
            ['date_from' => '2027-05-26', 'date_to' => '2027-06-05'],
            'Free Nights Offer',
            -220,
            [['usage' => [['age' => 21, 'amount' => 2]]]]
        );
    }

    /**
     * @test
     */
    public function works_only_in_validity_of_percentage_offer()
    {
        $this->checkDiscount(
            'Hotel J',
            ['date_from' => '2027-09-25', 'date_to' => '2027-10-06'],
            'Room Sharing with one free child',
            -320,
            [['usage' => [['age' => 21, 'amount' => 2], ['age' => 7, 'amount' => 1]]]]
        );
    }

    /**
     * @test
     */
    public function works_only_in_validity_of_price_row_offer()
    {
        $this->checkDiscount(
            'Hotel E',
            ['date_from' => '2027-07-25', 'date_to' => '2027-08-05'],
            'HB = BB meal plan upgrade',
            -36
        );
    }

    /**
     * @test
     */
    public function works_only_in_validity_of_textual_offer()
    {
        $this->checkDiscount(
            'Hotel A',
            ['date_from' => '2027-08-20', 'date_to' => '2027-09-04'],
            'Textual Offer',
            0
        );
    }

    /**
     * @test
     */
    public function works_only_in_validity_of_tiered_price_offer()
    {
        $this->checkDiscount(
            'Hotel J',
            ['date_from' => '2027-02-15', 'date_to' => '2027-02-22'],
            'Tiered pricing 6-9pax',
            300
        );
    }


}
