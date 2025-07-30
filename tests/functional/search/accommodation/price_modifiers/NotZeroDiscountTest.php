<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

/**
 * This test is for testing the following scenario:
 * Return the discount only if has discount value other than 0 except if it is a textual discount
 * (so not return it if it has 0 value and not textual)
 */
class NotZeroDiscountTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval) {
        return $this->prepareAccommodationSearchResult($interval, 'Hotel G', [['usage' => [['age' => 21, 'amount' => 1]]]]);
    }

    /**
     * @test
     */
    public function it_can_apply_normal_priceModifier() {
        $actual = $this->prepare(['date_from' => '2027-07-05', 'date_to' => '2027-07-09'])['results'][0][0]['prices'][0];
        $this->assertEquals(528, $actual['original_price']);
        $this->assertEquals(396, $actual['discounted_price']);
        $priceModifier = $actual['discounts'][0];
        foreach ($actual['discounts'] as $aD) {
            if ($aD['name']['en'] == 'Free night 4=3') {
                $priceModifier = $aD;
            }
        }
        $this->assertEquals('Free night 4=3', $priceModifier['name']['en']);
        $this->assertEquals(-132, $priceModifier['discount_value']);
    }

    /**
     * @test
     */
    public function it_can_apply_textual_priceModifier() {
        $actual = $this->prepare(['date_from' => '2027-07-05', 'date_to' => '2027-07-09'])['results'][0][0]['prices'][0];
        $this->assertEquals(528, $actual['original_price']);
        $this->assertEquals(396, $actual['discounted_price']);
        $priceModifier = $actual['discounts'][0];
        foreach ($actual['discounts'] as $aD) {
            if ($aD['name']['en'] == 'Textual Offer') {
                $priceModifier = $aD;
            }
        }
        $this->assertEquals('Textual Offer', $priceModifier['name']['en']);
        $this->assertEquals(0, $priceModifier['discount_value']);
        $this->assertEquals('textual', $priceModifier['offer']);
    }

    /**
     * @test
     */
    public function it_can_not_apply_price_modifier_with_zero_value() {
        $actual = $this->prepare(['date_from' => '2027-07-05', 'date_to' => '2027-07-09'])['results'][0][0]['prices'][0];
        $this->assertEquals(528, $actual['original_price']);
        $this->assertEquals(396, $actual['discounted_price']);
        $this->assertCount(2, $actual['discounts']);
        $appliedDiscountNames = [];
        foreach ($actual['discounts'] as $v) {
            $appliedDiscountNames[] = $v['name']['en'];
        }
        $this->assertNotContains('Zero Value Discount', $appliedDiscountNames);
    }

}
