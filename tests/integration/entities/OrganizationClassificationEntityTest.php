<?php

namespace Tests\Integration\Entities;

use App\Entities\OrganizationClassificationEntity;
use App\OrganizationClassification;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class OrganizationClassificationEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $organizationCl = OrganizationClassification::where('is_listable','=',1)->first();
        return [$organizationCl, (new OrganizationClassificationEntity($organizationCl))];
    }

    /**
     * @test
     */
    function an_orgclassification_can_present_admin_data() {
        list($orgCl, $orgClEntity) = $this->prepare_model_and_entity();
        $frontendData = $orgClEntity->getFrontendData(['admin']);

        $this->assertEquals($orgCl->id, $frontendData['id']);
        $this->assertEquals($orgCl->classification_taxonomy_id, $frontendData['taxonomy']['id']);
        $this->assertEquals($orgCl->value_taxonomy_id, $frontendData['value']['id']);
        $this->assertEquals($orgCl->additional_description_id, $frontendData['additional_description']['id']);
        $this->assertEquals($orgCl->is_highlighted, $frontendData['is_highlighted']);
        $this->assertEquals($orgCl->is_listable, $frontendData['is_listable']);
        $this->assertEquals(count($orgCl->childClassifications), count($frontendData['child_classifications']));
        $this->assertEquals(count($orgCl->childMetas), count($frontendData['child_metas']));
    }

    /**
     * @test
     */
    function an_orgclassification_present_frontend_data() {
        list($orgCl, $orgClEntity) = $this->prepare_model_and_entity();
        $frontendData = $orgClEntity->getFrontendData(['frontend']);

        $taxonomyData = $orgCl->classification_taxonomy_id ? (new TaxonomyEntity($orgCl->classificationTaxonomy))->translations() : null;
        $valueData = $orgCl->value_taxonomy_id ? (new TaxonomyEntity($orgCl->valueTaxonomy))->translations() : null;
        $descriptionData = $orgCl->additional_description_id ? (new DescriptionEntity($orgCl->additionalDescription))->getFrontendData() : null;

        $this->assertEquals($taxonomyData, $frontendData['name']);
        $this->assertEquals($valueData, $frontendData['value']);
        $this->assertEquals($descriptionData, $frontendData['additional_description']);
        $this->assertEquals($orgCl->is_highlighted, $frontendData['highlighted']);
        $this->assertEquals(count($orgCl->childClassifications), count($frontendData['child_classifications']));
        $this->assertEquals(count($orgCl->childMetas), count($frontendData['child_metas']));
    }
}