<?php

namespace Tests\Integration\Entities;

use App\DeviceClassification;
use App\Entities\DeviceClassificationEntity;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class DeviceClassificationEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $deviceCl = DeviceClassification::all()->first();
        return [$deviceCl, (new DeviceClassificationEntity($deviceCl))];
    }

    /**
     * @test
     */
    function a_device_can_present_classification_data() {
        list($devCl, $devClEntity) = $this->prepare_model_and_entity();
        $frontendData = $devClEntity->getFrontendData(['frontend']);

        $taxonomyData = $devCl->classification_taxonomy_id ? (new TaxonomyEntity($devCl->classificationTaxonomy))->translations() : null;
        $valueData = $devCl->value_taxonomy_id ? (new TaxonomyEntity($devCl->valueTaxonomy))->translations() : null;
        $descriptionData = $devCl->additional_description_id ? (new DescriptionEntity($devCl->additionalDescription))->getFrontendData() : null;

        $this->assertEquals($taxonomyData, $frontendData['name']);
        $this->assertEquals($valueData, $frontendData['value']);
        $this->assertEquals($descriptionData, $frontendData['additional_description']);
        $this->assertEquals($devCl->is_highlighted, $frontendData['highlighted']);
    }

    /**
     * @test
     */
    function a_device_can_present_classification_admin_data() {
        list($devCl, $devClEntity) = $this->prepare_model_and_entity();
        $frontendData = $devClEntity->getFrontendData(['admin']);

        $valueData = $devCl->value_taxonomy_id ? (new TaxonomyEntity($devCl->valueTaxonomy))->translations() : null;
        $descriptionData = $devCl->additional_description_id ? (new DescriptionEntity($devCl->additionalDescription))->getFrontendData() : null;

        $this->assertEquals($valueData, $frontendData['value']);
        $this->assertEquals($descriptionData, $frontendData['additional_description']);
        $this->assertEquals($devCl->is_highlighted, $frontendData['is_highlighted']);
        $this->assertEquals($devCl->is_listable, $frontendData['is_listable']);
    }
}