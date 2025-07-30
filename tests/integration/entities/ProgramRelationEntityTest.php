<?php
namespace Tests\Integration\Entities;

use App\Entities\ProgramRelationEntity;
use App\Entities\RelativeTimeEntity;
use App\ProgramRelation;
use Tests\TestCase;

class ProgramRelationEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models_and_entity(): array
    {
        $programRelation = ProgramRelation::first();
        return [$programRelation, (new ProgramRelationEntity($programRelation))];
    }

    /**
     * @test
     */
    function a_programRelation_has_basic_data()
    {
        list($programRelation, $programRelationEntity) = $this->prepare_models_and_entity();

        $expected = [
            'id' => $programRelation->id,
            'parent_id' => $programRelation->parent_id,
            'child_id' => $programRelation->child_id,
            'sequence' => $programRelation->sequence,
            'relative_time' => (new RelativeTimeEntity($programRelation->relativeTime))->getFrontendData(['time_of_day_taxonomy'])
        ];

        $programRelationData = $programRelationEntity->getFrontendData();
        $this->assertEquals($expected, $programRelationData);
    }

    /**
     * @test
     */
    function a_programRelation_has_extended_data()
    {
        list($programRelation, $programRelationEntity) = $this->prepare_models_and_entity();

        $programRelationData = $programRelationEntity->getFrontendData(['parent', 'child','embarkation']);
        $this->assertEquals($programRelation->parent_id, $programRelationData['parent']['id']);
        $this->assertEquals($programRelation->child_id, $programRelationData['child']['id']);
        
        $this->assertEquals($programRelation->embarkation_type_taxonomy_id, $programRelationData['embarkation_type']['id']);
        $this->assertEquals($programRelation->embarkation_direction_taxonomy_id, $programRelationData['embarkation_direction']['id']);
        
        
    }
}
