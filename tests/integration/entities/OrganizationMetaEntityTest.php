<?php

namespace Tests\Integration\Entities;

use App\Entities\OrganizationMetaEntity;
use App\OrganizationMeta;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class OrganizationMetaEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $organizationMt = OrganizationMeta::all()->first();
        return [$organizationMt, (new OrganizationMetaEntity($organizationMt))];
    }

    /**
     * @test
     */
    function an_organizationmeta_can_present_admin_data() {
        list($orgMt, $orgMtEntity) = $this->prepare_model_and_entity();
        $frontendData = $orgMtEntity->getFrontendData();

        $descriptionData = $orgMt->additional_description_id ? (new DescriptionEntity($orgMt->additionalDescription))->getFrontendData() : null;

        $this->assertEquals($orgMt->id, $frontendData['id']);
        $this->assertEquals($orgMt->taxonomy_id, $frontendData['taxonomy']['id']);
        $this->assertEquals($orgMt->value, $frontendData['value']);
        $this->assertEquals($orgMt->priority, $frontendData['priority']);
        $this->assertEquals($descriptionData, (is_null($orgMt->additional_description_id))? null: $frontendData['additional_description']);
        $this->assertEquals($orgMt->is_listable, $frontendData['is_listable']);
    }

    /**
     * @test
     */
    function an_organizationmeta_present_frontend_data() {
        list($orgMt, $orgMtEntity) = $this->prepare_model_and_entity();
        $frontendData = $orgMtEntity->getFrontendData(['frontend']);

        $taxonomyData = $orgMt->taxonomy_id ? (new TaxonomyEntity($orgMt->metaTaxonomy))->translations() : null;
        $descriptionData = $orgMt->additional_description_id ? (new DescriptionEntity($orgMt->additionalDescription))->getFrontendData() : null;

        $this->assertEquals($taxonomyData, $frontendData['name']);
        $this->assertEquals($orgMt->value, $frontendData['value']);
        $this->assertEquals($descriptionData, is_null($descriptionData)? null:$frontendData['additional_description']);

    }
}