<?php

namespace Tests\Integration\Entities;

use App\DeviceUsageElement;
use App\Entities\DeviceUsageElementEntity;
use App\Entities\AgeRangeEntity;
use Tests\TestCase;

class DeviceUsageElementEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $usageElement = DeviceUsageElement::all()->first();
        return [$usageElement, (new DeviceUsageElementEntity($usageElement))];
    }

    /**
     * @test
     */
    function a_usageelement_has_amount() {
        list($usageElement, $usageElementEntity) = $this->prepare_model_and_entity();
        $frontendData = $usageElementEntity->getFrontendData();
        $this->assertEquals($usageElement->amount, $frontendData['amount']);
    }

    /**
     * @test
     */
    function a_usagelement_has_agerange() {
        list($usageElement, $usageEntity) = $this->prepare_model_and_entity();
        $frontendData     = $usageEntity->getFrontendData();
        $this->assertEquals((new AgeRangeEntity($usageElement->ageRange))->getFrontendData(), $frontendData['age_range']);
    }

}