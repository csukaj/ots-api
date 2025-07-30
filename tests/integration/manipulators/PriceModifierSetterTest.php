<?php

namespace Tests\Integration\Manipulators;

use App\Entities\PriceModifierEntity;
use App\Manipulators\DateRangeSetter;
use App\Manipulators\PriceModifierSetter;
use App\Organization;
use App\PriceModifier;
use App\Facades\Config;
use Tests\TestCase;
use function factory;

class PriceModifierSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    protected function prepare_organization()
    {
        return factory(Organization::class, 'accommodation')->create();
    }

    protected function createDateRanges($organization, $from, $to)
    {
        return [
            $this->setDateRange($organization, $from, $to, 'open'),
            $this->setDateRange($organization, $from, $to, 'price_modifier'),
        ];
    }

    private function setDateRange($organization, $from, $to, $type)
    {
        $dateRange = [
            'name' => ['en' => $this->faker->word],
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => $from,
            'to_date' => $to,
            'type' => $type
        ];
        return (new DateRangeSetter($dateRange))->set();
    }

    /**
     * @test
     */
    function it_can_create_a_basic_priceModifier()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list($openDateRange, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'is_active' => true,
            'date_ranges' => [
                [
                    'id' => $priceModifierDateRange['id'],
                    'from_time' => $priceModifierDateRange['from_time'],
                    'to_time' => $priceModifierDateRange['to_time'],
                    'type' => 'price_modifier'
                ]
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'long_stay',
            'offer' => 'free_nights',
            'name' => [
                'en' => 'Test discount'
            ],
            'description' => [
                'en' => 'Test description'
            ],
            'condition_properties' => [
                'classifications' => [],
                'metas' => []
            ],
            'offer_properties' => [
                'classifications' => [],
                'metas' => []
            ],
            'promo_code' => $this->faker->word
        ];

        $setter = new PriceModifierSetter($data);
        $priceModifier = $setter->set()->attributesToArray();

        $this->assertEquals(Config::get('taxonomies.price_modifier_application_levels.room_request.price_modifier_condition_types.long_stay')['id'],
            $priceModifier['condition_taxonomy_id']);
        $this->assertEquals(Config::get('taxonomies.price_modifier_offers.free_nights')['id'],
            $priceModifier['offer_taxonomy_id']);
        $this->assertEquals($data['promo_code'], $priceModifier['promo_code']);
    }

    /**
     * @test
     */
    function it_can_have_condition_classification()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list($openDateRange, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'is_active' => true,
            'date_ranges' => [
                [
                    'id' => $priceModifierDateRange['id'],
                    'from_time' => $priceModifierDateRange['from_time'],
                    'to_time' => $priceModifierDateRange['to_time'],
                    'type' => 'price_modifier'
                ]
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'anniversary',
            'offer' => 'fixed_price',
            'name' => [
                'en' => 'Test discount'
            ],
            'description' => [
                'en' => 'Test description'
            ],
            'condition_properties' => [
                'classifications' => [
                    [
                        'name' => 'classification',
                        'value' => 'only_once',
                        'isset' => true
                    ]
                ],
                'metas' => []
            ],
            'offer_properties' => [
                'classifications' => [],
                'metas' => []
            ],
        ];

        $setter = new PriceModifierSetter($data);
        $priceModifier = $setter->set();
        $priceModifierEntity = (new PriceModifierEntity($priceModifier))->getFrontendData(['properties']);
        $this->assertEquals($data['condition'], $priceModifierEntity['condition']);
        $this->assertEquals($data['offer'], $priceModifierEntity['offer']);
        $this->assertEquals($data['condition_properties'], $priceModifierEntity['condition_properties']);
        $this->assertEquals($data['offer_properties'], $priceModifierEntity['offer_properties']);
    }

    /**
     * @test
     */
    function it_can_delete_a_condition_classification_by_update()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list(, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'is_active' => true,
            'date_ranges' => [
                [
                    'id' => $priceModifierDateRange['id'],
                    'from_time' => $priceModifierDateRange['from_time'],
                    'to_time' => $priceModifierDateRange['to_time'],
                    'type' => 'price_modifier'
                ]
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'anniversary',
            'offer' => 'free_nights',
            'name' => [
                'en' => 'Test discount'
            ],
            'description' => [
                'en' => 'Test description'
            ],
            'condition_properties' => [
                'classifications' => [
                    [
                        'name' => 'classification',
                        'value' => 'only_once'
                    ]
                ],
                'metas' => []
            ],
            'offer_properties' => [
                'classifications' => [],
                'metas' => []
            ],
        ];

        $setter = new PriceModifierSetter($data);
        $priceModifier = $setter->set();
        $priceModifierEntity = (new PriceModifierEntity($priceModifier))->getFrontendData(['properties']);

        $priceModifierEntity['condition_properties']['classifications'] = [];
        $setterSecond = new PriceModifierSetter($priceModifierEntity);
        $priceModifierSecond = $setterSecond->set();
        $priceModifierSecondEntity = (new PriceModifierEntity($priceModifierSecond))->getFrontendData(['properties']);

        $this->assertEquals($priceModifierEntity['id'], $priceModifierSecondEntity['id']);
        $this->assertEquals($priceModifierEntity['condition'], $priceModifierSecondEntity['condition']);
        $this->assertEquals($priceModifierEntity['offer'], $priceModifierSecondEntity['offer']);
        $this->assertEquals($priceModifierEntity['offer_properties'], $priceModifierSecondEntity['offer_properties']);
        $this->assertEquals($priceModifierEntity['condition_properties']['classifications'],
            $priceModifierSecondEntity['condition_properties']['classifications']);
    }

    /**
     * @test
     */
    function it_can_delete_a_condition_classification_when_isset_is_false_by_update()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list(, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'is_active' => true,
            'date_ranges' => [
                [
                    'id' => $priceModifierDateRange['id'],
                    'from_time' => $priceModifierDateRange['from_time'],
                    'to_time' => $priceModifierDateRange['to_time'],
                    'type' => 'price_modifier'
                ]
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'anniversary',
            'offer' => 'free_nights',
            'name' => [
                'en' => 'Test discount'
            ],
            'description' => [
                'en' => 'Test description'
            ],
            'condition_properties' => [
                'classifications' => [
                    [
                        'name' => 'classification',
                        'value' => 'only_once'
                    ]
                ],
                'metas' => []
            ],
            'offer_properties' => [
                'classifications' => [],
                'metas' => []
            ],
        ];

        $setter = new PriceModifierSetter($data);
        $priceModifier = $setter->set();
        $priceModifierEntity = (new PriceModifierEntity($priceModifier))->getFrontendData(['properties']);

        $priceModifierEntity['condition_properties']['classifications'] = [
            [
                'name' => 'classification',
                'value' => 'only_once',
                'isset' => false
            ]
        ];
        $setterSecond = new PriceModifierSetter($priceModifierEntity);
        $priceModifierSecond = $setterSecond->set();
        $priceModifierSecondEntity = (new PriceModifierEntity($priceModifierSecond))->getFrontendData(['properties']);

        $priceModifierEntity['condition_properties']['classifications'] = [];

        $this->assertEquals($priceModifierEntity['id'], $priceModifierSecondEntity['id']);
        $this->assertEquals($priceModifierEntity['condition'], $priceModifierSecondEntity['condition']);
        $this->assertEquals($priceModifierEntity['offer'], $priceModifierSecondEntity['offer']);
        $this->assertEquals($priceModifierEntity['offer_properties'], $priceModifierSecondEntity['offer_properties']);
        $this->assertEquals($priceModifierEntity['condition_properties']['classifications'],
            $priceModifierSecondEntity['condition_properties']['classifications']);
    }


    /**
     * @test
     */
    function it_can_create_condition_meta()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list($openDateRange, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'organization_id' => $organization->id,
            'is_active' => true,
            'date_ranges' => [
                [
                    'id' => $priceModifierDateRange['id'],
                    'from_time' => $priceModifierDateRange['from_time'],
                    'to_time' => $priceModifierDateRange['to_time'],
                    'type' => 'price_modifier'
                ]
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'anniversary',
            'offer' => 'free_nights',
            'name' => [
                'en' => 'Test discount'
            ],
            'description' => [
                'en' => 'Test description'
            ],
            'condition_properties' => [
                'classifications' => [
                    [
                        'name' => 'classification',
                        'value' => 'only_once'
                    ]
                ],
                'metas' => [
                    [
                        'name' => 'minimum_nights',
                        'value' => 3
                    ]
                ]
            ],
            'offer_properties' => [
                'classifications' => [],
                'metas' => []
            ],
        ];

        $setter = new PriceModifierSetter($data);
        $priceModifier = $setter->set();
        $priceModifierEntity = (new PriceModifierEntity($priceModifier))->getFrontendData(['properties']);
        $this->assertEquals($data['condition_properties']['metas'],
            $priceModifierEntity['condition_properties']['metas']);
    }

    /**
     * @test
     */
    function it_can_modify_price_modifier_type()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list($openDateRange, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'organization_id' => $organization->id,
            'is_active' => true,
            'date_ranges' => [
                [
                    'id' => $priceModifierDateRange['id'],
                    'from_time' => $priceModifierDateRange['from_time'],
                    'to_time' => $priceModifierDateRange['to_time'],
                    'type' => 'price_modifier'
                ]
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'anniversary',
            'offer' => 'free_nights',
            'name' => [
                'en' => 'Test discount'
            ],
            'description' => [
                'en' => 'Test description'
            ],
            'condition_properties' => [
                'classifications' => [
                    [
                        'name' => 'classification',
                        'value' => 'only_once'
                    ]
                ],
                'metas' => []
            ],
            'offer_properties' => [
                'classifications' => [],
                'metas' => []
            ],
        ];

        $setter = new PriceModifierSetter($data);
        $priceModifier = $setter->set();
        $priceModifierEntity = (new PriceModifierEntity($priceModifier))->getFrontendData(['properties']);

        $priceModifierEntity['modifier_type'] = 'Rule (mandatory, visible)';
        $priceModifierEntity['condition'] = 'early_bird';
        $priceModifierEntity['condition_properties']['classifications'] = [];
        $priceModifierEntity['condition_properties']['metas'] = [
            ['name' => 'minimum_nights', 'value' => 3],
            [
                'name' => 'booking_prior_minimum_days',
                'value' => 30
            ]
        ];
        $setterSecond = new PriceModifierSetter($priceModifierEntity);
        $priceModifierSecond = $setterSecond->set();
        $priceModifierSecondEntity = (new PriceModifierEntity($priceModifierSecond))->getFrontendData(['properties']);

        $this->assertEquals($priceModifierEntity['id'], $priceModifierSecondEntity['id']);
        $this->assertEquals($priceModifierEntity['modifier_type'], $priceModifierSecondEntity['modifier_type']);
        $this->assertEquals($priceModifierEntity['condition'], $priceModifierSecondEntity['condition']);
    }

    /**
     * @test
     */
    function it_can_create_a_price_modifier_with_correct_priority()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list(, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'is_active' => true,
            'date_ranges' => [
                [
                    'id' => $priceModifierDateRange['id'],
                    'from_time' => $priceModifierDateRange['from_time'],
                    'to_time' => $priceModifierDateRange['to_time'],
                    'type' => 'price_modifier'
                ]
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'long_stay',
            'offer' => 'percentage',
            'name' => ['en' => 'Test discount'],
            'description' => ['en' => 'Test description'],
            'condition_properties' => ['classifications' => [], 'metas' => []],
            'offer_properties' => ['classifications' => [], 'metas' => []]
        ];

        $priceModifier = (new PriceModifierSetter($data))->set()->attributesToArray();
        $this->assertEquals(Config::get('taxonomies.price_modifier_application_levels.room_request.price_modifier_condition_types.long_stay')['id'],
            $priceModifier['condition_taxonomy_id']);

        $firstDiscount = PriceModifier::forModel(Organization::class, $organization->id)->get()->first();
        $orderedDiscounts = $firstDiscount->findSiblingsInOrder(true);

        $data = [
            'is_active' => true,
            'date_ranges' => [
                [
                    'id' => $priceModifierDateRange['id'],
                    'from_time' => $priceModifierDateRange['from_time'],
                    'to_time' => $priceModifierDateRange['to_time'],
                    'type' => 'price_modifier'
                ]
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'long_stay',
            'offer' => 'free_nights',
            'name' => ['en' => 'Priority Test discount'],
            'description' => ['en' => 'Priority Test description'],
            'condition_properties' => [
                'classifications' => [],
                'metas' => []
            ],
            'offer_properties' => [
                'classifications' => [],
                'metas' => []
            ],
        ];

        $setter = new PriceModifierSetter($data);
        $newDiscount = $setter->set();
        $this->assertEquals(Config::get('taxonomies.price_modifier_offers.free_nights')['id'],
            $newDiscount->attributesToArray()['offer_taxonomy_id']);

        $orderedDiscounts->push($newDiscount);
        $expectedColledtion = PriceModifier::sortbyPriority($orderedDiscounts);
        $actualCollection = $firstDiscount->findSiblingsInOrder(true);

        $expectedArray = PriceModifierEntity::getCollection($expectedColledtion);
        $actualArray = PriceModifierEntity::getCollection($actualCollection);

        $this->assertEquals(count($expectedArray), count($actualArray));
        foreach ($expectedArray as $key => $expected) {
            $actual = $actualArray[$key];
            $this->assertEquals($expected['name']['en'], $actual['name']['en']);
            $this->assertEquals($key + 1, $actual['priority']);
        }
    }

    /**
     * @test
     */
    function it_can_create_an_annual_priceModifier()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list(, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'is_active' => true,
            'is_annual' => true,
            'date_ranges' => [
                $priceModifierDateRange
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'long_stay',
            'offer' => 'free_nights',
            'name' => ['en' => 'Test discount'],
            'description' => ['en' => 'Test description'],
            'condition_properties' => ['classifications' => [], 'metas' => []],
            'offer_properties' => ['classifications' => [], 'metas' => []],
            'promo_code' => null
        ];

        $priceModifier = (new PriceModifierSetter($data))->set()->attributesToArray();

        $this->assertEquals(Config::get('taxonomies.price_modifier_application_levels.room_request.price_modifier_condition_types.long_stay')['id'],
            $priceModifier['condition_taxonomy_id']);
        $this->assertEquals(Config::get('taxonomies.price_modifier_offers.free_nights')['id'],
            $priceModifier['offer_taxonomy_id']);
        $this->assertTrue($priceModifier['is_annual']);
    }

    /**
     * @test
     */
    function it_can_add_default_property_on_creation_and_only_on_creation()
    {
        $organization = $this->prepare_organization();
        $from = '2027-01-01';
        $to = '2027-01-30';
        list(, $priceModifierDateRange) = $this->createDateRanges($organization, $from, $to);

        $data = [
            'date_ranges' => [
                $priceModifierDateRange
            ],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'long_stay',
            'offer' => 'free_nights',
            'name' => ['en' => 'Test discount'],
            'description' => ['en' => 'Test description'],
            'condition_properties' => ['classifications' => [], 'metas' => []],
            'offer_properties' => ['classifications' => [], 'metas' => []],
            'promo_code' => null
        ];

        $priceModifier = (new PriceModifierSetter($data))->set();


        $this->assertEquals(Config::get('taxonomies.price_modifier_offers.free_nights')['id'],
            $priceModifier->offer_taxonomy_id);
        $this->assertCount(1, $priceModifier->offerClassifications);
        $actual = $priceModifier->offerClassifications[0]->toArray();
        $this->assertEquals(Config::get('taxonomies.price_modifier_offers.free_nights.classification'),
            $actual['classification_taxonomy_id']);
        $this->assertEquals(Config::get('taxonomies.price_modifier_offers.free_nights.classifications.use_last_consecutive_night'),
            $actual['value_taxonomy_id']);

        $data['id'] = $priceModifier->id;
        $priceModifierUpdated = (new PriceModifierSetter($data))->set();
        $this->assertCount(0, $priceModifierUpdated->offerClassifications);
    }

}