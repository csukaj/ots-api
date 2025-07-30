<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use App\Facades\Config;
use App\OfferClassification;
use App\PriceModifier;
use Tests\TestCase;

class DiscountValidityOnBorderTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare($interval)
    {
        return $this->prepareAccommodationSearchResult(
            $interval,
            'Hotel J',
            [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_has_a_correct_discount_value_when_holiday_range_is_in_discount_range()
    {
        PriceModifier::destroy(73);
        $expected = [
            'original_price' => '220.0',
            'order_itemable_index' => 0,
            'discounted_price' => '198.0',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => ['en' => 'Discount range end'],
                        'offer' => 'percentage',
                        'discount_percentage' => -10.0,
                        'discount_value' => -22.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -10.0,
                    'value' => -22.0,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-07', 'date_to' => '2027-02-09']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);

    }

    /**
     * @test
     */
    public function it_has_a_correct_discount_value_when_discount_range_at_holiday_end()
    {
        PriceModifier::destroy(73);
        $expected = [
            'original_price' => '330.0',
            'order_itemable_index' => 0,
            'discounted_price' => '297.0',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => ['en' => 'Discount range end'],
                        'offer' => 'percentage',
                        'discount_percentage' => -10.0,
                        'discount_value' => -33.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -10.0,
                    'value' => -33.0,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-07', 'date_to' => '2027-02-10']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);

        $expected = [
            'original_price' => '440.0',
            'order_itemable_index' => 0,
            'discounted_price' => '396.0',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => ['en' => 'Discount range end'],
                        'offer' => 'percentage',
                        'discount_percentage' => -10.0,
                        'discount_value' => -44.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -10.0,
                    'value' => -44.0,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-07', 'date_to' => '2027-02-11']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);

    }

    /**
     * @test
     */
    public function it_has_a_correct_discount_value_when_holiday_is_longer()
    {
        PriceModifier::destroy(73);
        $expected = [
            'original_price' => '770.0',
            'order_itemable_index' => 0,
            'discounted_price' => '726.0',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => [
                            'en' => 'Textual Offer with meal plan restriction',
                            'ru' => 'Текстология предложение',
                            'de' => 'Textangebot',
                            'hu' => 'Szöveges Ajánlat bizonyos ellátásra'
                        ],
                        'offer' => 'textual',
                        'discount_percentage' => 0.0,
                        'discount_value' => 0,
                        'modifier_type' => 491,
                        'condition' => 'long_stay',
                        'description' => [
                            'ru' => 'Это текстуальное предложение',
                            'de' => 'Dies ist ein Textangebot',
                            'en' => 'This is a textual offer',
                            'hu' => 'Ez egy szöveges ajánlat'
                        ]
                    ],
                    [
                        'name' => ['en' => 'Discount range end'],
                        'offer' => 'percentage',
                        'discount_percentage' => -5.71,
                        'discount_value' => -44.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -5.71,
                    'value' => -44.0,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-06', 'date_to' => '2027-02-13']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);

    }

    /**
     * @test
     */
    public function discount_range_border_is_calculated_correctly()
    {
        PriceModifier::destroy(73);
        $expected = [
            'original_price' => '220.0',
            'order_itemable_index' => 0,
            'discounted_price' => '209.0',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => [
                            'en' => 'Textual Offer with meal plan restriction',
                            'ru' => 'Текстология предложение',
                            'de' => 'Textangebot',
                            'hu' => 'Szöveges Ajánlat bizonyos ellátásra'
                        ],
                        'offer' => 'textual',
                        'discount_percentage' => 0.0,
                        'discount_value' => 0,
                        'modifier_type' => 491,
                        'condition' => 'long_stay',
                        'description' => [
                            'ru' => 'Это текстуальное предложение',
                            'de' => 'Dies ist ein Textangebot',
                            'en' => 'This is a textual offer',
                            'hu' => 'Ez egy szöveges ajánlat'
                        ]
                    ],
                    [
                        'name' => ['en' => 'Discount range end'],
                        'offer' => 'percentage',
                        'discount_percentage' => -5.0,
                        'discount_value' => -11.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -5.0,
                    'value' => -11.0,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-06', 'date_to' => '2027-02-08']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);
        /////////////////////

        $expected = [
            'original_price' => '220.0',
            'order_itemable_index' => 0,
            'discounted_price' => '209.0',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => ['en' => 'Discount range end'],
                        'offer' => 'percentage',
                        'discount_percentage' => -5.0,
                        'discount_value' => -11.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -5.0,
                    'value' => -11.0,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-10', 'date_to' => '2027-02-12']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);
        /////////////////////
        $expected = [
            'original_price' => '220.0',
            'order_itemable_index' => 0,
            'discounted_price' => '220.0',
            'meal_plan' => 'b/b',
            'discounts' => [],
            'meal_plan_id' => '2',

        ];

        $result = $this->prepare(['date_from' => '2027-02-11', 'date_to' => '2027-02-13']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);

    }


    /**
     * @test
     */
    public function it_respects__do_not_apply_previous_discounts__setting()
    {

        $expected = [
            'original_price' => '220.0',
            'order_itemable_index' => 0,
            'discounted_price' => '176.0',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => ['en' => 'Discount range end'],
                        'offer' => 'percentage',
                        'discount_percentage' => -10.0,
                        'discount_value' => -22.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                    [
                        'name' => ['en' => 'Previous discounts not applied'],
                        'offer' => 'percentage',
                        'discount_percentage' => -10.0,
                        'discount_value' => -22.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -20.0,
                    'value' => -44.0,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-07', 'date_to' => '2027-02-09']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);

        OfferClassification
            ::where('price_modifier_id', 73)
            ->where('value_taxonomy_id',
                Config::getOrFail('taxonomies.price_modifier_offers.percentage.classifications.do_not_apply_previous_price_modifiers'))
            ->delete();

        $expected = [
            'original_price' => '220.0',
            'order_itemable_index' => 0,
            'discounted_price' => '178.2',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => ['en' => 'Discount range end'],
                        'offer' => 'percentage',
                        'discount_percentage' => -10.0,
                        'discount_value' => -22.0,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                    [
                        'name' => ['en' => 'Previous discounts not applied'],
                        'offer' => 'percentage',
                        'discount_percentage' => -9.0,
                        'discount_value' => -19.8,
                        'modifier_type' => 491,
                        'condition' => 'minimum_nights',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -19.0,
                    'value' => -41.8,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-07', 'date_to' => '2027-02-09']);
        $actual = $result['results'][0][0]['prices'][0];

        $this->assertEquals($expected, $actual);

    }

}
