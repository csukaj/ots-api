<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class ChildNthRoomDiscountTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $orgId = 1, $rooms = null) {
        $roomsDefault = [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        return $this->prepareAccommodationSearchResult($interval, $orgId, is_null($rooms) ? $roomsDefault: $rooms)['results'];
    }

    /**
     * @test
     */
    public function it_can_apply_priceModifier() {
        $rooms = [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]],
                ['usage' => [['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-12'], 2, $rooms)[1][0]['prices'];
        
        $this->assertEquals(660, $actual[0]['original_price']);
        $this->assertEquals(66, $actual[0]['discounted_price']);
        $priceModifier = $actual[0]['discounts'][0];
        $this->assertEquals('Child room discount', $priceModifier['name']['en']);
        $this->assertEquals(-594, $priceModifier['discount_value']);
    }
    
    /**
     * @test
     */
    public function it_can_not_apply_price_modifier_for_less_child() {
         $rooms = [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 5, 'amount' => 1]]]
        ];
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-12'], 2, $rooms)[1][0]['prices'];
        
        $this->assertEquals(660, $actual[0]['original_price']);
        $this->assertEquals(528, $actual[0]['discounted_price']);
        $priceModifier = $actual[0]['discounts'][0];
        $this->assertEquals('Early bird offer /w Free nights /w B/B', $priceModifier['name']['en']);
    }

    /**
     * @test
     */
    public function it_cannot_apply_price_modifier_in_the_same_room() {
        $rooms = [
                ['usage' => [['age' => 21, 'amount' => 1],['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]],
        ];
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-12'], 2, $rooms)[0][0]['prices'];
        
        $this->assertEquals(1370, $actual[0]['original_price']);
        $this->assertEquals(1096, $actual[0]['discounted_price']);
        $priceModifier = $actual[0]['discounts'][0];
        $this->assertEquals('Early bird offer /w Free nights /w B/B', $priceModifier['name']['en']);
    }
    
    /**
     * @test
     */
    public function it_cannot_apply_price_modifier_in_the_3rd_child_room() {
        $rooms = [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 5, 'amount' => 1]]],
                ['usage' => [['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]],
                ['usage' => [['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-12'], 2, $rooms)[3][0]['prices'];
        
        $this->assertEquals(660, $actual[0]['original_price']);
        $this->assertEquals(528, $actual[0]['discounted_price']);
        $priceModifier = $actual[0]['discounts'][0];
        $this->assertEquals('Early bird offer /w Free nights /w B/B', $priceModifier['name']['en']);
        
    }

    /**
     * @test
     */
    public function it_cannot_apply_price_modifier_when_age_not_matches() {
        $rooms = [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 17, 'amount' => 1], ['age' => 17, 'amount' => 1]]]
        ];
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-12'], 2, $rooms)[1][0]['prices'];

        $this->assertEquals(660, $actual[0]['original_price']);
        $this->assertEquals(528, $actual[0]['discounted_price']);
        $priceModifier = $actual[0]['discounts'][0];
        $this->assertEquals('Early bird offer /w Free nights /w B/B', $priceModifier['name']['en']);
    }

    /**
     * @test
     */
    public function it_cannot_apply_price_modifier_when_too_many_child() {
        $rooms = [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-12'], 2, $rooms)[1][0]['prices'];

        $this->assertEquals(1370, $actual[0]['original_price']);
        $this->assertEquals(1096, $actual[0]['discounted_price']);
        $priceModifier = $actual[0]['discounts'][0];
        $this->assertEquals('Early bird offer /w Free nights /w B/B', $priceModifier['name']['en']);
    }
    
    /**
     * @test
     */
    public function it_cannot_apply_price_modifier_when_child_is_too_young() {
        $rooms = [
                ['usage' => [['age' => 21, 'amount' => 2]]],
                ['usage' => [['age' => 2, 'amount' => 1], ['age' => 5, 'amount' => 1]]],
                ['usage' => [['age' => 5, 'amount' => 1], ['age' => 5, 'amount' => 1]]]
        ];
        $actual = $this->prepare(['date_from' => '2027-06-07', 'date_to' => '2027-06-12'], 2, $rooms)[1][0]['prices'];
        
        $this->assertEquals(660, $actual[0]['original_price']);
        $this->assertEquals(528, $actual[0]['discounted_price']);
        $priceModifier = $actual[0]['discounts'][0];
        $this->assertEquals('Early bird offer /w Free nights /w B/B', $priceModifier['name']['en']);
    }

}
