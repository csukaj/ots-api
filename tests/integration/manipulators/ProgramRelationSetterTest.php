<?php

use App\Entities\ProgramRelationEntity;
use App\Entities\RelativeTimeEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Location;
use App\Manipulators\ProgramRelationSetter;
use App\Program;
use App\ProgramRelation;
use App\RelativeTime;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class ProgramRelationSetterTest extends TestCase
{

    /**
     * @test
     */
    function it_can_set_a_new_relation()
    {
        $newPrograms = $this->generatePrograms();

        $relativeTime = (new RelativeTimeEntity(RelativeTime::first()))->getFrontendData();
        $data = [
            'parent_id' => Program::all()->first()->id,
            'child_id' => $newPrograms[0]->id,
            'sequence' => 1,
            'relative_time' => $relativeTime
        ];

        $relation = (new ProgramRelationSetter($data))->set();
        $this->assertInstanceOf(ProgramRelation::class, $relation);
        $this->assertNotEmpty($relation->id);
        $this->assertEquals($data['parent_id'], $relation->parent_id);
        $this->assertEquals($data['sequence'], $relation->sequence);
        $this->assertEquals($data['relative_time']['id'], $relation->relative_time_id);
    }

    /**
     * @test 
     */
    function it_can_update_a_relation()
    {
        $newPrograms = $this->generatePrograms();
        $data = (new ProgramRelationEntity(ProgramRelation::first()))->getFrontendData();
        $data['child_id'] = $newPrograms[0]->id;

        $relation = (new ProgramRelationSetter($data))->set();
        $this->assertInstanceOf(ProgramRelation::class, $relation);
        $this->assertEquals($data['id'], $relation->id);
        $relationArray = (new ProgramRelationEntity($relation))->getFrontendData();
        unset($relationArray['updated_at']);
        $this->assertEquals($data, $relationArray);
    }

    /**
     * @test 
     */
    function it_can_not_create_existing_relation()
    {
        $data = ProgramRelation::first()->attributesToArray();
        unset($data['id']);

        $this->expectException(UserException::class);
        $relation = (new ProgramRelationSetter($data))->set();
    }

    /**
     * @return array
     */
    private function generatePrograms($count = 1): array
    {
        $programs = [];
        for ($i = 0; $i < $count; $i++) {
            $programs[] = factory(Program::class)->create(['location_id' => Location::first()->id]);
        }
        return $programs;
    }

    /**
     * @test
     */
    function it_can_set_embarkation_properties()
    {
        $newPrograms = $this->generatePrograms();

        $relativeTime = (new RelativeTimeEntity(RelativeTime::first()))->getFrontendData();
        $data = [
            'parent_id' => Program::all()->first()->id,
            'child_id' => $newPrograms[0]->id,
            'sequence' => 1,
            'relative_time' => $relativeTime,
            'embarkation_type' => (new TaxonomyEntity(Taxonomy::find(Config::getOrFail('taxonomies.embarkation_types.financial'))))->getFrontendData(['translations']),
            'embarkation_direction' => (new TaxonomyEntity(Taxonomy::find(Config::getOrFail('taxonomies.embarkation_directions.2-way'))))->getFrontendData(['translations'])
        ];

        $relation = (new ProgramRelationSetter($data))->set();
        $this->assertInstanceOf(ProgramRelation::class, $relation);
        $this->assertNotEmpty($relation->id);
        $this->assertEquals($data['embarkation_type']['id'], $relation->embarkation_type_taxonomy_id);
        $this->assertEquals($data['embarkation_direction']['id'], $relation->embarkation_direction_taxonomy_id);
    }
}
