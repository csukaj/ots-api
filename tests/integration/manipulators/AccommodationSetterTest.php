<?php

namespace Tests\Integration\Manipulators;

use App\Accommodation;
use App\Entities\AccommodationEntity;
use App\Manipulators\OrganizationSetter;
use Tests\TestCase;

class AccommodationSetterTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_set_classification_property() {

        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create4'
            ],
            'is_active' => 1,
            'properties' => [
                "Accommodation Category" => [
                    "name" => "Accommodation Category",
                    "value" => "Guest House",
                    "category" => "general",
                    'is_listable' => true
                ],
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ],
            
        ];

        $expected = $data['properties']["Accommodation Category"];


        $orgSetter = new OrganizationSetter($data);
        $orgObj = $orgSetter->set();
        $this->assertInstanceOf(Accommodation::class, $orgObj);
        $orgEntity = new AccommodationEntity($orgObj);
        $frontend = $orgEntity->getFrontendData(['info']);

        $actual = null;

        foreach ($frontend['properties'] as $category) {
            if ($category['name']['en'] == 'General') {
                $actual = $category['child_classifications'][0];
            }
        }

        $this->assertNotEmpty($actual);
        $this->assertEquals($expected['name'], $actual['name']['en']);
        $this->assertEquals($expected['value'], $actual['value']['en']);
    }

    /**
     * @test
     */
    function it_can_set_meta_property() {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create5'
            ],
            'is_active' => 1,
            'properties' => [
                [
                    'name' => 'Built in',
                    'value' => 1996,
                    'listable' => true
                ],
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $orgSetter = new OrganizationSetter($data);
        $orgObj = $orgSetter->set();
        $this->assertInstanceOf(Accommodation::class, $orgObj);
        $orgEntity = new AccommodationEntity($orgObj);
        $frontend = $orgEntity->getFrontendData(['info']);
        $this->assertEquals($data['properties'][0]['name'], $frontend['properties'][0]['name']['en']);
        $this->assertEquals($data['properties'][0]['value'], $frontend['properties'][0]['value']);
    }

    /**
     * @test
     */
    function it_can_set_meta_and_classification() {
        $data = [
            'type' => 'accommodation',
            'name' => [
                'en' => 'Hotel Test Create6'
            ],
            'is_active' => 1,
            'properties' => [
                [
                    'name' => 'Built in',
                    'value' => 1996,
                    "category" => "general",
                    'is_listable' => true
                ],
                [
                    "name" => "Accommodation Category",
                    "value" => "Guest House",
                    "category" => "general",
                    'is_listable' => true
                ],
                ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ];

        $organization = (new OrganizationSetter($data))->set();
        $this->assertInstanceOf(Accommodation::class, $organization);
        
        $frontend = (new AccommodationEntity($organization))->getFrontendData(['info']);
        foreach ($data['properties'] as $expected) {
            if(empty($expected['is_listable'])){
                continue;
            }
            $found = false;
            foreach ($frontend['properties'] as $property) {
                if ($property['name']['en'] == $expected['name']) {
                    $value = is_string($property['value']) ? $property['value'] : $property['value']['en'];
                    $this->assertEquals($expected['value'], $value);
                    $found = true;
                    break;
                }
                if (isset($property['child_classifications'])) {
                    foreach ($property['child_classifications'] as $actual) {
                        if ($actual['name']['en'] == $expected['name']) {
                            $value = is_string($actual['value']) ? $actual['value'] : $actual['value']['en'];
                            $this->assertEquals($expected['value'], $value);
                            $found = true;
                            break;
                        }
                    }
                }
            }
            $this->assertTrue($found);
        }
    }

}
