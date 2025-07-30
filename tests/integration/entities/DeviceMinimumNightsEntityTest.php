<?php

namespace Tests\Integration\Entities;

use App\DeviceMinimumNights;
use App\Entities\DeviceMinimumNightsEntity;
use Tests\TestCase;

class DeviceMinimumNightsEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $minNights = DeviceMinimumNights::all()->first();
        return [$minNights, (new DeviceMinimumNightsEntity($minNights))];
    }

    /**
     * @test
     */
    function a_deviceminimumnights_has_basic_data() {
        list($minNights, $minNightsEntity) = $this->prepare_model_and_entity();
        $frontendData = $minNightsEntity->getFrontendData();
        $this->assertEquals($minNights->id, $frontendData['id']);
        $this->assertEquals($minNights->device_id, $frontendData['device_id']);
        $this->assertEquals($minNights->date_range_id, $frontendData['date_range_id']);
        $this->assertEquals($minNights->minimum_nights, $frontendData['minimum_nights']);
    }

    
}