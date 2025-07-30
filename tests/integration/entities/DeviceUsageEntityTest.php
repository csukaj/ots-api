<?php

namespace Tests\Integration\Entities;

use App\DeviceUsage;
use App\Entities\DeviceUsageElementEntity;
use App\Entities\DeviceUsageEntity;
use Tests\TestCase;

class DeviceUsageEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $usage = DeviceUsage::all()->first();
        return [$usage, (new DeviceUsageEntity($usage))];
    }

    /**
     * @test
     */
    function a_usage_has_a_device() {
        list($usage, $usageEntity) = $this->prepare_model_and_entity();
        $frontendData = $usageEntity->getFrontendData(['admin']);

        $this->assertEquals($usage->device_id, $frontendData['device_id']);
    }

    /**
     * @test
     */
    function a_usage_has_usageelements() {
        list($usage, $usageEntity) = $this->prepare_model_and_entity();
        $frontendData = $usageEntity->getFrontendData(['admin']);

        $elements = $usage->elements;
        $this->assertEquals(count($elements), count($frontendData['elements']));

        for ($i = 0; $i < count($elements); $i++) {
            $uEl = new DeviceUsageElementEntity($elements[$i]);
            $this->assertEquals($uEl->getFrontendData(), $frontendData['elements'][$i]);
        }
    }

    /**
     * @test
     */
    function a_usage_has_public_id() {
        list($usage, $usageEntity) = $this->prepare_model_and_entity();
        $frontendData = $usageEntity->getFrontendData();
        $this->assertEquals($usage->id, $frontendData['id']);
    }
    
}