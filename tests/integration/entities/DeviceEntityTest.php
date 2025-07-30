<?php

namespace Tests\Integration\Entities;

use App\DeviceClassification;
use App\DeviceMeta;
use App\Entities\DeviceClassificationEntity;
use App\Entities\DeviceEntity;
use App\Entities\DeviceMetaEntity;
use App\Organization;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class DeviceEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models_and_entity() {
        $organization = Organization::findOrFail(1);
        $device = $organization->devices[0];
        return [$organization, $device, (new DeviceEntity($device))];
    }

    private function compare_usage_and_usage_data($usage, $usageData) {
        foreach ($usage->elements as $usageElement) {
            $ageRangeName = $usageElement->ageRange->name->name;
            $this->assertTrue(array_key_exists($ageRangeName, $usageData));
            $this->assertEquals($usageElement->amount, $usageData[$ageRangeName]);
        }
    }

    /**
     * @test
     */
    function a_device_has_a_name() {
        list(, $device, $deviceEntity) = $this->prepare_models_and_entity();
        $frontendData = $deviceEntity->getFrontendData();

        $this->assertEquals($device->name->name, $frontendData['name']['en']);
    }

    /**
     * @test
     */
    function a_device_has_usages() {
        list(, $device, $deviceEntity) = $this->prepare_models_and_entity();
        $frontendData = $deviceEntity->getFrontendData(['usages']);

        $usages = $device->usages;
        $this->assertEquals(count($usages), count($frontendData['usages']));
    }

    /**
     * @test
     */
    function a_device_has_prices() {
        list(, $device, $deviceEntity) = $this->prepare_models_and_entity();
        $frontendData = $deviceEntity->getFrontendData(['prices']);
        foreach ($frontendData['products'] as $product) {
            foreach ($product['prices'] as $price) {
                $this->assertTrue(!!$price['id']);
                $this->assertTrue(!!$price['name']['name']);
                $this->assertTrue(!!$price['age_range']);
                $this->assertTrue(isset($price['amount']));
                $this->assertTrue(isset($price['extra']));
                $this->assertTrue(!!count($price['elements']));
            }
        }
    }

    /**
     * @test
     */
    function a_device_has_margin() {
        list(, $device, $deviceEntity) = $this->prepare_models_and_entity();
        $frontendData = $deviceEntity->getFrontendData(['margin']);

        $this->assertEquals($device->margin_value, $frontendData['margin_value']);
        $this->assertEquals($device->margin_type_taxonomy_id ? $device->marginType->name : null, $frontendData['margin_type']);
    }

    /**
     * @test
     */
    function a_device_has_descriptions() {
        list(, $device, $deviceEntity) = $this->prepare_models_and_entity();
        $frontendData = $deviceEntity->getFrontendData(['descriptions']);

        $this->assertTrue(!empty($frontendData['descriptions']));

        foreach ($device->descriptions as $description) {
            $descriptionTxName = $description->descriptionTaxonomy->name;
            $desc = (new DescriptionEntity($description->description))->getFrontendData();
            $this->assertEquals($desc, $frontendData['descriptions'][$descriptionTxName]);
        }
    }

    /**
     * @test
     */
    function a_device_has_metas() {
        list(, $device, $deviceEntity) = $this->prepare_models_and_entity();
        $frontendData = $deviceEntity->getFrontendData(['properties']);
        $metas = (new DeviceMeta())->where('device_id', $device->id)->get();
        $mtEntities = DeviceMetaEntity::getCollection($metas,['frontend']);
        $this->assertEquals($mtEntities, $frontendData['metas']);
    }

    /**
     * @test
     */
    function a_device_has_classifications() {
        list(, $device, $deviceEntity) = $this->prepare_models_and_entity();
        $frontendData = $deviceEntity->getFrontendData(['properties']);
        $classifications = (new DeviceClassification())
            ->where('device_id', $device->id)
            ->forParent(null)
            ->orderBy('priority')
            ->get();
        $clsEntities = DeviceClassificationEntity::getCollection($classifications, ['frontend']);
        $this->assertEquals($clsEntities, $frontendData['classifications']);
    }

    /**
     * @test
     */
    function a_device_has_images() {
        list(,, $deviceEntity) = $this->prepare_models_and_entity();
        $frontendData = $deviceEntity->getFrontendData(['images']);
        $this->assertNotEmpty($frontendData['images']);
    }
}