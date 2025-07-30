<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class TextualTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval, $hotelName = 'Hotel A')
    {
        return $this->prepareAccommodationSearchResult($interval, $hotelName,
            [['usage' => [['age' => 21, 'amount' => 1]]]]);
    }

    /**
     * @test
     */
    public function it_can_find_a_textual_price_modifier_amongst_others()
    {
        $actual = $this->prepare([
            'date_from' => '2027-08-06',
            'date_to' => '2027-08-09'
        ])['results'][0][0]['prices'][0];
        $this->assertEquals('Textual Offer', $actual['discounts'][2]['name']['en']);
        $this->assertEquals('This is a textual offer', $actual['discounts'][2]['description']['en']);
        $this->assertEquals(0, $actual['discounts'][2]['discount_value']);
    }

    /**
     * @test
     */
    public function it_can_find_a_lone_textual_price_modifier()
    {
        $actual = $this->prepare([
            'date_from' => '2027-09-06',
            'date_to' => '2027-09-09'
        ])['results'][0][0]['prices'][0];
        $this->assertEquals('Textual Offer', $actual['discounts'][0]['name']['en']);
        $this->assertEquals('This is a textual offer', $actual['discounts'][0]['description']['en']);
        $this->assertEquals(0, $actual['discounts'][0]['discount_value']);
    }

    /**
     * @test
     */
    public function it_can_find_a_meal_plan_restricted_textual_price_modifier()
    {
        $actual = $this->prepare(['date_from' => '2027-02-02', 'date_to' => '2027-02-05'],
            'Hotel J')['results'][0][0]['prices'];
        $expected = [
            [
                'original_price' => '330.0',
                'meal_plan_id' => '2',
                'discounted_price' => '330.0',
                'order_itemable_index' => 0,
                'meal_plan' => 'b/b',
                'discounts' =>
                    [
                        [
                            'description' =>
                                [
                                    'ru' => 'Это текстуальное предложение',
                                    'de' => 'Dies ist ein Textangebot',
                                    'en' => 'This is a textual offer',
                                    'hu' => 'Ez egy szöveges ajánlat',
                                ],
                            'offer' => 'textual',
                            'discount_percentage' => 0.0,
                            'discount_value' => 0,
                            'modifier_type' => 491,
                            'condition' => 'long_stay',
                            'name' =>
                                [
                                    'ru' => 'Текстология предложение',
                                    'de' => 'Textangebot',
                                    'en' => 'Textual Offer with meal plan restriction',
                                    'hu' => 'Szöveges Ajánlat bizonyos ellátásra',
                                ],
                        ],
                    ],
                'total_discount' => ['percentage' => 0.0, 'value' => 0.0],
            ],
            [
                'original_price' => '330.0',
                'meal_plan_id' => '3',
                'order_itemable_index' => 0,
                'discounts' => [],
                'discounted_price' => '330.0',
                'meal_plan' => 'h/b',
            ],
            [
                'original_price' => '330.0',
                'meal_plan_id' => '4',
                'order_itemable_index' => 0,
                'discounts' => [],
                'discounted_price' => '330.0',
                'meal_plan' => 'f/b',
            ],
        ];
        $this->assertEquals($expected, $actual);
    }

}
