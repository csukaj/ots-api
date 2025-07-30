<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class FreeNightsInContinousPeriodsTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval) {
        return $this->prepareAccommodationSearchResult(
                $interval, 
                'Hotel J',
                [['usage' => [['age' => 21, 'amount' => 1]]]]
                );
    }
    
    private function commonTests($expected,$actual){
        $this->assertCount(1, $actual['devices']);
        $this->assertEquals($expected['room_name'], $actual['devices'][0]['name']['en']);
        $this->assertEquals($expected['original_price'], $actual['original_price']);
        $this->assertEquals($expected['discounted_price'], $actual['discounted_price']);
        $this->assertEquals($expected['total_discount'], $actual['total_discount']);
        $this->assertEquals($expected['meal_plan'], $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_gives_price_modifier_for_separate_periods() {
        $expected = [
            'room_name'=>'Comfy Room',
            'original_price'=>660,
            'discounted_price'=>550,
            'total_discount'=>['value' => 110, 'percentage' => 16.67],
            'meal_plan'=>'b/b'
            ];
        $actual = $this->prepare(['date_from' => '2027-04-01', 'date_to' => '2027-04-07'])['best_price'];
        $this->commonTests($expected, $actual);
        
        $actual = $this->prepare(['date_from' => '2027-04-08', 'date_to' => '2027-04-14'])['best_price'];
        $this->commonTests($expected, $actual);
        
        
        $actual = $this->prepare(['date_from' => '2027-04-22', 'date_to' => '2027-04-28'])['best_price'];
        $this->commonTests($expected, $actual);
    }

    /**
     * @test
     */
    public function it_gives_price_modifier_for_2_merged_periods() {
        $actual = $this->prepare(['date_from' => '2027-04-06', 'date_to' => '2027-04-12'])['best_price'];
        $expected = [
            'room_name'=>'Comfy Room',
            'original_price'=>660,
            'discounted_price'=>550,
            'total_discount'=>['value' => 110, 'percentage' => 16.67],
            'meal_plan'=>'b/b'
            ];
        $this->commonTests($expected, $actual);
    }

    /**
     * @test
     */
    public function it_doesnt_give_price_modifier_for_2_periods_with_gap_when_minimum_night_condition_doesnt_match() {
        $actual = $this->prepare(['date_from' => '2027-04-13', 'date_to' => '2027-04-22'])['best_price'];
        $expected = [
            'room_name'=>'Comfy Room',
            'original_price'=>990,
            'discounted_price'=>990,
            'total_discount'=>['value' => 0, 'percentage' => 0],
            'meal_plan'=>'b/b'
            ];
        $this->commonTests($expected, $actual);
    }

    /**
     * @test
     */
    public function it_gives_price_modifier_for_2_merged_periods_plus_gap_plus_1_period_when_condition_met_only_in_merged_part() {
        $actual = $this->prepare(['date_from' => '2027-04-04', 'date_to' => '2027-04-24'])['best_price'];
        $expected = [
            'room_name'=>'Comfy Room',
            'original_price'=>2200,
            'discounted_price'=>1980,
            'total_discount'=>['value' => 220, 'percentage' => 10],
            'meal_plan'=>'b/b'
            ];
        $this->commonTests($expected, $actual);
    }

    /**
     * @test
     */
    public function it_gives_price_modifier_for_2_merged_periods_plus_gap_plus_1_period_when_condition_met_everywhere() {
        $actual = $this->prepare(['date_from' => '2027-04-04', 'date_to' => '2027-04-27'])['best_price'];
        $expected = [
            'room_name'=>'Comfy Room',
            'original_price'=>2530,
            'discounted_price'=> 2200,
            'total_discount'=>['value' => 330, 'percentage' => 13.04],
            'meal_plan'=>'b/b'
            ];
        $this->commonTests($expected, $actual);
    }

}
