<?php

namespace Tests\Integration\Entities;

use App\Entities\LocationEntity;
use App\Organization;
use Tests\TestCase;

class LocationEntityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    
    private function prepare_models_and_entity() {
        $organization = Organization::findOrFail(1);
        $location = $organization->location;
        return [$organization, $location, (new LocationEntity($location))];
    }
    
    /**
     * @test
     */
    function a_location_has_id() {
        list(, $location, $locationEntity) = $this->prepare_models_and_entity();
        $frontendData = $locationEntity->getFrontendData(['frontend']);
        $this->assertEquals($location->id, $frontendData['id']);
    }
    
    /**
     * @test
     */
    function a_location_has_an_island() {
        list(, , $locationEntity) = $this->prepare_models_and_entity();
        $frontendData = $locationEntity->getFrontendData(['frontend']);
        $this->assertEquals('Mahé', $frontendData['island']);
    }
    
    /**
     * @test
     */
    function a_location_has_a_district() {
        list(, , $locationEntity) = $this->prepare_models_and_entity();
        $frontendData = $locationEntity->getFrontendData(['frontend']);
        $this->assertEquals('Anse aux Pins', $frontendData['district']);
    }
    
    /**
     * @test
     */
    function a_location_has_latitude() {
        list(, , $locationEntity) = $this->prepare_models_and_entity();
        $frontendData = $locationEntity->getFrontendData(['frontend']);
        $this->assertEquals('-4.6930494', $frontendData['latitude']);
    }
    
    /**
     * @test
     */
    function a_location_has_longitude() {
        list(, , $locationEntity) = $this->prepare_models_and_entity();
        $frontendData = $locationEntity->getFrontendData(['frontend']);
        $this->assertEquals('55.5173608', $frontendData['longitude']);
    }
    
    /**
     * @test
     */
    function a_location_has_a_po_box() {
        list(, , $locationEntity) = $this->prepare_models_and_entity();
        $frontendData = $locationEntity->getFrontendData(['frontend']);
        $this->assertEquals('012345', $frontendData['po_box']);
    }
    
}
