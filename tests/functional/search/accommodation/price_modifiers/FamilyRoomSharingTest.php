<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class FamilyRoomSharingTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($usages) {
        return $this->prepareAccommodationSearchResult(
                ['date_from' => '2027-08-02', 'date_to' => '2027-08-08'], 
                'Hotel J',
                $usages
                );
    }

    /**
     * @test
     */
    public function it_is_applicable() {
        $usages = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 2],
                    ['age' => 10, 'amount' => 2]
                ]
            ]
        ];
        $actual = $this->prepare($usages)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(2160, $actual['original_price']);
        $this->assertEquals(1760, $actual['discounted_price']);
        $this->assertEquals(['value' => 400, 'percentage' => 18.52], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_is_not_applicable_thanks_to_missing_child() {
        $usages = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 2]
                ]
            ]
        ];
        $actual = $this->prepare($usages)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1200, $actual['original_price']);
        $this->assertEquals(1200, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

    /**
     * @test
     */
    public function it_is_not_applicable_thanks_to_minimum_age() {
        $usages = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 2],
                    ['age' => 4, 'amount' => 2]
                ]
            ]
        ];
        $actual = $this->prepare($usages)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(2160, $actual['original_price']);
        $this->assertEquals(2160, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }
    
    /**
     * @test
     */
    public function it_is_not_applicable_thanks_to_child_headcount_minimum() {
        $usages = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 2],
                    ['age' => 4, 'amount' => 1]
                ]
            ]
        ];
        $actual = $this->prepare($usages)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(1680, $actual['original_price']);
        $this->assertEquals(1680, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }
    
    /**
     * @test
     */
    public function it_is_not_applicable_thanks_to_adult_headcount_maximum() {
        $usages = [
            [
                'usage' => [
                    ['age' => 21, 'amount' => 3],
                    ['age' => 4, 'amount' => 1]
                ]
            ]
        ];
        $actual = $this->prepare($usages)['best_price'];
        $this->assertEquals(1, count($actual['devices']));
        $this->assertEquals('Comfy Room', $actual['devices'][0]['name']['en']);
        $this->assertEquals(2760, $actual['original_price']);
        $this->assertEquals(2760, $actual['discounted_price']);
        $this->assertEquals(['value' => 0, 'percentage' => 0], $actual['total_discount']);
        $this->assertEquals('b/b', $actual['meal_plan']);
    }

}
