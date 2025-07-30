<?php

namespace Tests\Integration\Entities;

use App\Entities\ProgramClassificationEntity;
use App\ProgramClassification;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class ProgramClassificationEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $programCl = ProgramClassification::where('is_listable','=',1)->first();
        return [$programCl, (new ProgramClassificationEntity($programCl))];
    }

    /**
     * @test
     */
    function a_program_classification_can_present_admin_data() {
        list($progCl, $progClEntity) = $this->prepare_model_and_entity();
        $frontendData = $progClEntity->getFrontendData(['admin']);

        $this->assertEquals($progCl->id, $frontendData['id']);
        $this->assertEquals($progCl->classification_taxonomy_id, $frontendData['taxonomy']['id']);
        $this->assertEquals($progCl->value_taxonomy_id, $frontendData['value']['id']);
        $this->assertEquals($progCl->additional_description_id, $frontendData['additional_description']['id']);
        $this->assertEquals($progCl->is_highlighted, $frontendData['is_highlighted']);
        $this->assertEquals($progCl->is_listable, $frontendData['is_listable']);
        $this->assertEquals(count($progCl->childClassifications), count($frontendData['child_classifications']));
        $this->assertEquals(count($progCl->childMetas), count($frontendData['child_metas']));
    }

    /**
     * @test
     */
    function a_program_classification_present_frontend_data() {
        list($progCl, $progClEntity) = $this->prepare_model_and_entity();
        $frontendData = $progClEntity->getFrontendData(['frontend']);

        $taxonomyData = $progCl->classification_taxonomy_id ? (new TaxonomyEntity($progCl->classificationTaxonomy))->translations() : null;

        $this->assertEquals($taxonomyData, $frontendData['name']);
    }
}