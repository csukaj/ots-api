<?php

namespace Tests\Integration\Entities;

use App\DeviceMeta;
use App\Entities\DeviceMetaEntity;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class DeviceMetaEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $deviceMt = DeviceMeta::all()->first();
        return [$deviceMt, (new DeviceMetaEntity($deviceMt))];
    }

    /**
     * @test
     */
    function a_devicemeta_present_frontend_data() {
        list($devMt, $devMtEntity) = $this->prepare_model_and_entity();
        $frontendData = $devMtEntity->getFrontendData(['frontend']);

        $taxonomyData = $devMt->taxonomy_id ? (new TaxonomyEntity($devMt->metaTaxonomy))->translations() : null;
        $descriptionData = $devMt->additional_description_id ? (new DescriptionEntity($devMt->additionalDescription))->getFrontendData() : null;

        $this->assertEquals($taxonomyData, $frontendData['name']);
        $this->assertEquals($devMt->value, $frontendData['value']);
        $this->assertEquals($descriptionData, is_null($descriptionData) ? null : $frontendData['additional_description']);
    }

    /**
     * @test
     */
    function a_devicemeta_can_present_admin_data() {
        list($devMt, $devMtEntity) = $this->prepare_model_and_entity();
        $frontendData = $devMtEntity->getFrontendData(['admin']);

        $taxonomyData = $devMt->taxonomy_id ? (new TaxonomyEntity($devMt->metaTaxonomy))->getFrontendData(['translations','translations_with_plurals']) : null;
        $descriptionData = $devMt->additional_description_id ? (new DescriptionEntity($devMt->additionalDescription))->getFrontendData() : null;

        $this->assertEquals($taxonomyData, $frontendData['taxonomy']);
        $this->assertEquals($devMt->value, $frontendData['value']);
        $this->assertEquals($descriptionData, is_null($descriptionData) ? null : $frontendData['additional_description']);
    }
}