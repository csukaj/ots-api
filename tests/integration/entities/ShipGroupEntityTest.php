<?php

namespace Tests\integration\entities;


use App\Entities\AgeRangeEntity;
use App\Entities\DeviceEntity;
use App\Entities\LocationEntity;
use App\Entities\ShipGroupEntity;
use App\OrganizationGroup;
use App\ShipGroup;
use Modules\Stylersmedia\Entities\GalleryEntity;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class ShipGroupEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity(): array
    {
        $shipGroup = ShipGroup::first();
        return [$shipGroup, (new ShipGroupEntity($shipGroup))];
    }

    /**
     * @test
     */
    function it_can_present_organization_group_data()
    {
        list($shipGroup, $shipGroupEntity) = $this->prepare_model_and_entity();
        $frontendData = $shipGroupEntity->getFrontendData();

        $expected =
            [
                "id" => $shipGroup->id,
                "name" => (new DescriptionEntity($shipGroup->name))->getFrontendData(),
                "type" => $shipGroup->type->name,
                "is_active" => $shipGroup->is_active,
                "properties" => [],
                "amount" => $shipGroup->getShipCount(),
                'deviceable_type' => ShipGroup::class
            ];

        $this->assertEquals($expected, $frontendData);
    }

    /**
     * @test
     */
    function it_can_present_organization_group_data_with_children()
    {
        list($shipGroup, $shipGroupEntity) = $this->prepare_model_and_entity();
        $frontendData = $shipGroupEntity->getFrontendData(['info']);


        $expected = array(
            "id" => $shipGroup->id,
            'deviceable_type' => ShipGroup::class,
            "name" => (new DescriptionEntity($shipGroup->name))->getFrontendData(),
            "type" => $shipGroup->type->name,
            "is_active" => $shipGroup->is_active,
            "amount" => $shipGroup->getShipCount(),
            'locations' => LocationEntity::getCollection($shipGroup->getHomePortLocations(), ['frontend']),
            'devices' => [
                "cabin" => DeviceEntity::getCollection($shipGroup->devices,
                    ['descriptions', 'properties', 'images', 'amount'])
            ],
            'descriptions' => [],
            'galleries' => GalleryEntity::getCollection($shipGroup->galleries),
            'settings' => [],
            'age_ranges' => AgeRangeEntity::getCollection($shipGroup->ageRanges, ['frontend']),
            'search_options' => [
                'Ship Group Category' => 'Catamaran',
                'Propulsion' => 'Sailing boat'
            ],
            'optional_fees' => [
                ['name' => ['en' => 'Skipper in Period A'], 'rack_price' => 150],
                ['name' => ['en' => 'Skipper/Cook (1 person) in Period B, C, D'], 'rack_price' => 174],
                [
                    'name' => ['en' => 'Starter pack (bottle of rum, bottle of whiskey, 6-pack of Bud beer, 10 can of beans and 10 box of Puffin marmalade)'],
                    'rack_price' => 42
                ]
            ],
        );

        $this->assertArrayHasKey('properties', $frontendData);
        unset($frontendData['properties']);
        $this->assertEquals($expected, $frontendData);

    }
}
