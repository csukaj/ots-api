<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class MealPlanRestrictionDiscountTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $orgId = 1) {
        return $this->prepareAccommodationSearchResult($interval, $orgId, [['usage' => [['age' => 21, 'amount' => 1]]]]);
    }

    /**
     * @test
     */
    public function it_can_apply_normal_price_modifier_without_restriction() {
        $actual = $this->prepare(['date_from' => '2027-07-05', 'date_to' => '2027-07-09'], 7)['results'][0][0]['prices'][0];
        $this->assertEquals(528, $actual['original_price']);
        $this->assertEquals(396, $actual['discounted_price']);
        $appliedDiscountNames = [];
        foreach ($actual['discounts'] as $v) {
            $appliedDiscountNames[] = $v['name']['en'];
            if ($v['name']['en'] == 'Free night 4=3') {
                $priceModifier = $v;
            }
        }
        $this->assertContains('Free night 4=3', $appliedDiscountNames);
        $this->assertEquals(-132, $priceModifier['discount_value']);
    }

    /**
     * @test
     */
    function it_can_apply_price_modifier_to_allowed_meal_plan() {
        $actual = $this->prepare(['date_from' => '2027-07-05', 'date_to' => '2027-07-09'], 5)['results'][0][0]['prices'];

        $runtests = false;

        foreach ($actual as $price) {
            if ($price['meal_plan'] == 'b/b') {
                $this->assertNotEmpty($price['discounts']);
                $this->assertEquals('Meal Plan Restricted discount', $price['discounts'][0]['name']['en']);
                $runtests = true;
            }
        }
        $this->assertTrue($runtests);
    }

    /**
     * @test
     */
    function it_can_not_apply_price_modifier_to_denied_meal_plan() {
        $actual = $this->prepare(['date_from' => '2027-07-05', 'date_to' => '2027-07-09'], 5)['results'][0][0]['prices'];

        $runtests = false;

        foreach ($actual as $price) {
            if ($price['meal_plan'] == 'h/b') {
                $this->assertEmpty($price['discounts']);
                $runtests = true;
            }
        }
        $this->assertTrue($runtests);
    }

}
