<?php
namespace Tests\Integration\Manipulators;

use App\Entities\OrganizationGroupPoiEntity;
use App\Entities\PoiEntity;
use App\Manipulators\OrganizationGroupPoiSetter;
use App\OrganizationGroup;
use App\Poi;
use App\OrganizationGroupPoi;
use Tests\TestCase;

class OrganizationGroupOrganizationGroupPoiSetterTest extends TestCase
{

    /**
     * @test
     */
    function it_can_set_a_new_poi()
    {


        $data = [
        'type' => 'Home Port',
        'organization_group_id' => OrganizationGroup::first()->id,
        'poi' => (new PoiEntity(Poi::first()))->getFrontendData()
        ];

        $orgGroupPoi = (new OrganizationGroupPoiSetter($data))->set();
        $this->assertInstanceOf(OrganizationGroupPoi::class, $orgGroupPoi);
        $this->assertNotEmpty($orgGroupPoi->id);
        $this->assertEquals($data['type'], $orgGroupPoi->type->name);
        $this->assertEquals($data['organization_group_id'], $orgGroupPoi->organization_group_id);
        $this->assertEquals($data['poi'], (new PoiEntity($orgGroupPoi->poi))->getFrontendData());
    }

    /**
     * @test 
     */
    function it_can_update_a_poi()
    {

        $data = (new OrganizationGroupPoiEntity(OrganizationGroupPoi::first()))->getFrontendData();
        $data['poi'] = (new PoiEntity(Poi::all()->last()))->getFrontendData();

        $poi = (new OrganizationGroupPoiSetter($data))->set();
        $this->assertInstanceOf(OrganizationGroupPoi::class, $poi);
        $this->assertEquals($data['id'], $poi->id);
        $poiArray = (new OrganizationGroupPoiEntity($poi))->getFrontendData();
        unset($poiArray['updated_at']);
        $this->assertEquals($data, $poiArray);
    }
}
