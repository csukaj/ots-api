<?php
namespace Tests\Integration\Relations;

use App\Organization;
use App\Relations\DevicesJSONRelation;
use App\Relations\DevicesRelation;
use App\Relations\Relation;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class DevicesRelationDerivatesTest extends TestCase
{

    /**
     * @test
     */
    function it_can_list_options_for_device_relation()
    {
        $relation = new DevicesRelation(new Taxonomy(), Organization::findOrFail(1));
        $data = $relation->getFrontendData();
        $this->assertCount(3, $data['options']);
        $this->assertEquals(Relation::FORMAT_CSV, $data['format']);
    }

    /**
     * @test
     */
    function it_can_list_options_for_device_json_relation()
    {
        $relation = new DevicesJSONRelation(new Taxonomy(), Organization::findOrFail(1));
        $data = $relation->getFrontendData();
        $this->assertCount(3, $data['options']);
        $this->assertEquals(Relation::FORMAT_JSON, $data['format']);
    }
}
