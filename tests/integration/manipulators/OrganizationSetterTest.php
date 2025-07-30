<?php

namespace Tests\Integration\Manipulators;

use App\AgeRange;
use App\Entities\HotelChainEntity;
use App\Entities\OrganizationEntity;
use App\Exceptions\UserException;
use App\HotelChain;
use App\Manipulators\OrganizationSetter;
use App\Organization;
use Illuminate\Support\Facades\Config;
use Modules\Stylersmedia\Entities\Gallery;
use Tests\TestCase;

class OrganizationSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     * @throws \Exception
     */
    function it_can_create_a_hotel()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create1'
            ],
            'parent' => (new HotelChainEntity(HotelChain::find(101)))->getFrontendData(),
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $orgSetter = new OrganizationSetter($data);
        $orgObj = $orgSetter->set();
        $this->assertInstanceOf(Organization::class, $orgObj);
        $this->assertEquals(Config::get('taxonomies.organization_types.accommodation.id'), $orgObj->type_taxonomy_id);
        $this->assertEquals($data['name']['en'], $orgObj->name->description);
        $this->assertEquals($data['parent']['name']['en'], $orgObj->parentOrganization->name->description);

        //test default created items

        $ageRanges = AgeRange::forAgeRangeable(Organization::class, $orgObj->id)->get();
        $this->assertCount(1, $ageRanges);
        $defaultAgeRange = $ageRanges[0];
        $this->assertEquals('adult', $defaultAgeRange->name->name);
        $this->assertEquals(0, $defaultAgeRange->from_age);
        $this->assertNull($defaultAgeRange->to_age);

        $expected_gallery = [
            'galleryable_id' => $orgObj->id,
            'galleryable_type' => Organization::class,
            'role_taxonomy_id' => Config::get('taxonomies.gallery_roles.frontend_gallery')
        ];
        $galleryCount = Gallery::where($expected_gallery)->count();
        $this->assertEquals(1, $galleryCount);
    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_update_hotel()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create2'
            ],
            'parent' => (new HotelChainEntity(HotelChain::find(101)))->getFrontendData(),
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $orgSetter = new OrganizationSetter($data);
        $orgObj = $orgSetter->set();
        $this->assertInstanceOf(Organization::class, $orgObj);
        $this->assertEquals(Config::get('taxonomies.organization_types.accommodation.id'), $orgObj->type_taxonomy_id);
        $this->assertEquals($data['name']['en'], $orgObj->name->description);
        $this->assertEquals($data['parent']['name']['en'], $orgObj->parentOrganization->name->description);

        $update = [
            'id' => $orgObj->id,
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Update'
            ]
        ];

        $orgSetter = new OrganizationSetter($update);
        $orgUpObj = $orgSetter->set();
        $this->assertInstanceOf(Organization::class, $orgObj);
        $this->assertEquals($orgObj->id, $orgUpObj->id);
        $this->assertEquals(Config::get('taxonomies.organization_types.accommodation.id'), $orgUpObj->type_taxonomy_id);
        $this->assertEquals($update['name']['en'], $orgUpObj->name->description);
        $this->assertEmpty($orgUpObj->parentOrganization);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_can_activate_hotel()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create3'
            ],
            'is_active' => 1,
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $orgSetter = new OrganizationSetter($data);
        $orgObj = $orgSetter->set();
        $this->assertInstanceOf(Organization::class, $orgObj);
        $this->assertTrue((bool)$orgObj->is_active);
    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_set_short_description()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create7'
            ],
            'descriptions' => [
                'short_description' => [
                    'en' => 'Lorem ipsum'
                ]
            ],
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $orgSetter = new OrganizationSetter($data);
        $orgObj = $orgSetter->set();
        $this->assertInstanceOf(Organization::class, $orgObj);
        $orgEntity = new OrganizationEntity($orgObj);
        $frontend = $orgEntity->getFrontendData(['descriptions']);
        $this->assertEquals($data['descriptions']['short_description'], $frontend['descriptions']['short_description']);
    }

    /**
     * @test
     * @throws UserException
     * @throws \Exception
     */
    function it_can_set_long_description_too()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create8'
            ],
            'descriptions' => [
                'short_description' => [
                    'en' => 'Lorem ipsum'
                ],
                'long_description' => [
                    'en' => 'Lorem ipsum long'
                ]
            ],
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $orgSetter = new OrganizationSetter($data);
        $orgObj = $orgSetter->set();
        $this->assertInstanceOf(Organization::class, $orgObj);
        $orgEntity = new OrganizationEntity($orgObj);
        $frontend = $orgEntity->getFrontendData(['descriptions']);
        $this->assertEquals($data['descriptions']['short_description'], $frontend['descriptions']['short_description']);
        $this->assertEquals($data['descriptions']['long_description'], $frontend['descriptions']['long_description']);
    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_delete_descriptions()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create11'
            ],
            'descriptions' => [
                'short_description' => [
                    'en' => 'Lorem ipsum'
                ],
                'long_description' => [
                    'en' => 'Lorem ipsum long'
                ]
            ],
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $orgObj = (new OrganizationSetter($data))->set();
        $this->assertInstanceOf(Organization::class, $orgObj);

        $data['id'] = $orgObj->id;
        $data['descriptions'] = [];

        $orgObjUpd = (new OrganizationSetter($data))->set();
        $this->assertInstanceOf(Organization::class, $orgObjUpd);
        $frontend = (new OrganizationEntity($orgObjUpd))->getFrontendData(['descriptions']);
        $this->assertEmpty($frontend['descriptions']);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_cannot_set_hotel_with_already_used_name()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create X'
            ],
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $orgSetter = new OrganizationSetter($data);
        $orgObj = $orgSetter->set();
        $this->expectException(UserException::class);
        $orgSetter2 = new OrganizationSetter($data);
        $orgObj2 = $orgSetter2->set();
    }

    /**
     * @test
     */
    function it_cannot_set_hotel_without_default_classifications()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create X'
            ]
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageRegExp('/A required classification is not set:/');
        $orgSetter2 = new OrganizationSetter($data);
        $orgObj2 = $orgSetter2->set();
    }

    /**
     * @test
     * @throws UserException
     */
    function it_can_set_short_name()
    {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => $this->faker->word
            ],
            'short_name' => [
                'en' => $this->faker->word
            ],
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $organization = (new OrganizationSetter($data))->set();
        $this->assertInstanceOf(Organization::class, $organization);
        $this->assertEquals($data['short_name']['en'], $organization->shortName->description);

        $data['id'] = $organization->id;
        $data['short_name']['en'] = $this->faker->word;

        $organizationUpdated = (new OrganizationSetter($data))->set();
        $this->assertEquals($organization->id, $organizationUpdated->id);
        $this->assertEquals($data['short_name']['en'], $organizationUpdated->shortName->description);
    }
}
