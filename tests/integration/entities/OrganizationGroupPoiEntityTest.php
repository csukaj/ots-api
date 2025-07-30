<?php
namespace Tests\Integration\Entities;

use App\Entities\OrganizationGroupPoiEntity;
use App\Entities\PoiEntity;
use App\OrganizationGroupPoi;
use Tests\TestCase;

class OrganizationGroupPoiEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity(): array
    {
        $poi = OrganizationGroupPoi::all()->first();
        return [$poi, (new OrganizationGroupPoiEntity($poi))];
    }

    /**
     * @test
     */
    function it_can_present_poi_data()
    {
        list($organizationGroupPoi, $organizationGroupPoiEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationGroupPoiEntity->getFrontendData();

        $expected = [
            'id' => $organizationGroupPoi->id,
            'organization_group_id' => $organizationGroupPoi->organization_group_id,
            'type' => $organizationGroupPoi->type->name,
            'poi' => (new PoiEntity($organizationGroupPoi->poi))->getFrontendData()
        ];

        $this->assertEquals($expected, $frontendData);
    }
}
