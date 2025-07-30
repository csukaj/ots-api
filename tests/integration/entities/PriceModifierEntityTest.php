<?php

namespace Tests\Integration\Entities;

use App\PriceModifierClassification;
use App\PriceModifierMeta;
use App\Entities\PriceModifierEntity;
use App\OfferClassification;
use App\OfferMeta;
use App\Organization;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class PriceModifierEntityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    
    private function prepare_models_and_entity() {
        $organization = Organization::findOrFail(1);
        $priceModifier = $organization->dateRanges()
            ->priceModifier()->get()[0]
            ->priceModifierPeriods[0]
            ->priceModifier;
        return [$organization, $priceModifier, (new PriceModifierEntity($priceModifier))];
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_id() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        $this->assertEquals($priceModifier->id, $frontendData['id']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_a_name() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        
        $this->assertEquals((new DescriptionEntity($priceModifier->name))->getFrontendData(), $frontendData['name']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_a_description() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        
        $this->assertEquals($priceModifier->description ? (new DescriptionEntity($priceModifier->description))->getFrontendData() : null, $frontendData['description']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_a_condition() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        
        $this->assertEquals($priceModifier->condition->name, $frontendData['condition']);
    }

    /**
     * @test
     */
    function a_price_modifier_has_a_price_modifier_type() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();

        $this->assertEquals($priceModifier->modifierType->name, $frontendData['modifier_type']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_an_offer_type() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        
        $this->assertEquals($priceModifier->offer->name, $frontendData['offer']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_can_have_condition_metas() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData(['properties']);
        $model = new PriceModifierMeta();
        $this->assertEquals($model->getMetaEntities('price_modifier_id', $priceModifier->id), $frontendData['condition_properties']['metas']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_can_have_condition_classifications() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData(['properties']);
        $model = new PriceModifierClassification();
        $this->assertEquals($model->getClassificationEntities('price_modifier_id', $priceModifier->id), $frontendData['condition_properties']['classifications']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_can_have_offer_metas() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData(['properties']);
        $model = new OfferMeta();
        $this->assertEquals($model->getMetaEntities('price_modifier_id', $priceModifier->id), $frontendData['offer_properties']['metas']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_can_have_offer_classifications() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData(['properties']);
        $model = new OfferClassification();
        $this->assertEquals($model->getClassificationEntities('price_modifier_id', $priceModifier->id), $frontendData['offer_properties']['classifications']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_priority() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        
        $this->assertEquals($priceModifier->priority, $frontendData['priority']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_promo_code() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        
        $this->assertEquals($priceModifier->promo_code, $frontendData['promo_code']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_is_active_and_is_annual_property() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        
        $this->assertEquals($priceModifier->is_active, $frontendData['is_active']);
        $this->assertEquals($priceModifier->is_annual, $frontendData['is_annual']);
    }
    
    /**
     * @test
     */
    function a_price_modifier_has_price_modifiable() {
        list(, $priceModifier, $priceModifierEntity) = $this->prepare_models_and_entity();
        $frontendData = $priceModifierEntity->getFrontendData();
        
        $this->assertEquals($priceModifier->dateRanges()->first()->date_rangeable_type, $frontendData['price_modifiable_type']);
        $this->assertEquals($priceModifier->dateRanges()->first()->date_rangeable_id, $frontendData['price_modifiable_id']);
    }
}
