<?php

namespace Tests\Functional\Search\Accommodation\PriceModifiers;

use Tests\TestCase;

class UseMandatoryTest extends TestCase
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
                        ['age' => 21, 'amount' => 2]
                    ]
                ],
                [
                    'usage' => [
                        ['age' => 6, 'amount' => 1],
                        ['age' => 7, 'amount' => 1]
                    ]
                ]
            ]
        );
    }

    /**
     * @test
     */
    public function it_has_a_correct_discount_value_for_extras_when_use_mandatory_is_defined()
    {

        $expected = [
            'original_price' => '800.0',
            'order_itemable_index' => 1,
            'discounted_price' => '400.0',
            'meal_plan' => 'b/b',
            'discounts' =>
                [
                    [
                        'name' => ['en' => 'Mandatory pricing on deduction base prices'],
                        'offer' => 'percentage',
                        'discount_percentage' => -50.0,
                        'discount_value' => -400.0,
                        'modifier_type' => 491,
                        'condition' => 'room_sharing',
                        'description' => null,
                    ],
                ],
            'meal_plan_id' => '2',
            'total_discount' =>
                [
                    'percentage' => -50.0,
                    'value' => -400.0,
                ],
        ];

        $result = $this->prepare(['date_from' => '2027-02-15', 'date_to' => '2027-02-19']);
        $actual = $result['results'][1][0]['prices'][0];

        $this->assertEquals($expected, $actual);

    }

}
