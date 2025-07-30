<?php

namespace Tests\Functional\Search\Accommodation;

use App\Entities\Search\AccommodationSearchEntity;
use App\Island;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class PriceModifierFuncTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_find_price_modifiers()
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendDataList = $accommodationSearchEntity->setParameters([
            'interval' => [
                'date_from' => '2027-07-06',
                'date_to' => '2027-07-09'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ]
        ])->getFrontendData();

        foreach ($frontendDataList as $frontendData) {
            $this->assertTrue(isset($frontendData['results']));
        }
    }

    /**
     * @test
     */
    function it_can_find_price_modifiers_in_correct_order_so_percentage_price_modifier_is_calculated_with_already_applied_price_modifiers(
    )
    {
        $accommodationSearchEntity = new AccommodationSearchEntity();
        $frontendData = $accommodationSearchEntity->setParameters([
            'organizations' => [1],
            'interval' => [
                'date_from' => '2027-07-06',
                'date_to' => '2027-07-09'
            ],
            'usages' => [
                [
                    'usage' => [
                        ['age' => 21, 'amount' => 1]
                    ]
                ]
            ]
        ])->getFrontendData()[1];

        $expected =
            [
                [
                    'name' => ['en' => 'Free Nights Offer'],
                    'discount_percentage' => -33.329999999999998,
                    'offer' => 'free_nights',
                    'discount_value' => -113.3,
                    'description' => null,
                    'modifier_type' => 491,
                    'condition' => 'long_stay'
                ],
                [
                    'name' => ['en' => 'Long Stay Percentage Based On B/B Price'],
                    'discount_percentage' => -6.3799999999999999,
                    'offer' => 'percentage',
                    'discount_value' => -21.670000000000002,
                    'description' => null,
                    'modifier_type' => 491,
                    'condition' => 'long_stay'
                ],
                [
                    'name' =>
                        [
                            'ru' => 'Текстология предложение',
                            'de' => 'Textangebot',
                            'en' => 'Textual Offer',
                            'hu' => 'Szöveges Ajánlat',
                        ],
                    'discount_percentage' => 0.0,
                    'offer' => 'textual',
                    'discount_value' => 0,
                    'description' =>
                        [
                            'ru' => 'Это текстуальное предложение',
                            'de' => 'Dies ist ein Textangebot',
                            'en' => 'This is a textual offer',
                            'hu' => 'Ez egy szöveges ajánlat',
                        ],
                    'modifier_type' => 491,
                    'condition' => 'long_stay'
                ]
            ];

        $actual = $frontendData['results'][0][0]['prices'][1]['discounts'];
        $this->assertEquals($expected, $actual);

    }
}
