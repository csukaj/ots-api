<?php

namespace Tests\Integration\Entities;

use App\Entities\PriceElementEntity;
use App\Organization;
use Tests\TestCase;

class PriceElementEntityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    
    private function prepare_models_and_entity() {
        $organization = Organization::findOrFail(1);
        $device = $organization->devices[0];
        $priceElement = $device->products[0]->prices[0]->elements[0];
        return [$organization, $device, $priceElement, (new PriceElementEntity($priceElement))];
    }
    
    /**
     * @test
     */
    function a_price_element_has_public_data() {
        list(, , $priceElement, $priceElementEntity) = $this->prepare_models_and_entity();
        
        $priceElementData = $priceElementEntity->getFrontendData();
        $this->assertEquals($priceElement->date_range_id, $priceElementData['date_range_id']);
        $this->assertEquals($priceElement->modelMealPlan->mealPlan->name->name, $priceElementData['meal_plan']);
        $this->assertEquals($priceElement->rack_price, $priceElementData['rack_price']);
    }

    /**
     * @test
     */
    function a_price_element_has_admin_data() {
        list(, , $priceElement, $priceElementEntity) = $this->prepare_models_and_entity();
        
        $priceElementData = $priceElementEntity->getFrontendData(['admin']);
        $this->assertEquals($priceElement->date_range_id, $priceElementData['date_range_id']);
        $this->assertEquals($priceElement->modelMealPlan->mealPlan->name->name, $priceElementData['meal_plan']);
        $this->assertEquals($priceElement->rack_price, $priceElementData['rack_price']);
        $this->assertEquals($priceElement->price_id, $priceElementData['price_id']);
        $this->assertEquals($priceElement->net_price, $priceElementData['net_price']);
        $this->assertEquals($priceElement->marginType ? $priceElement->marginType->name : null, $priceElementData['margin_type']);
        $this->assertEquals($priceElement->margin_value, $priceElementData['margin_value']);
    }
}
