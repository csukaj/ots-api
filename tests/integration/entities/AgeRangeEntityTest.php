<?php

namespace Tests\Integration\Entities;

use App\Entities\AgeRangeEntity;
use App\Organization;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class AgeRangeEntityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models_and_entity() {
        $organization = Organization::findOrFail(1);
        $ageRange = $organization->ageRanges[0];
        return [$organization, $ageRange, (new AgeRangeEntity($ageRange))];
    }
    
    /**
     * @test
     */
    function an_age_range_has_basic_data() {
        list(, $ageRange, $ageRangeEntity) = $this->prepare_models_and_entity();
        $frontendData = $ageRangeEntity->getFrontendData();
        
        $this->assertEquals($ageRange->id, $frontendData['id']);
        $this->assertEquals($ageRange->age_rangeable_type, $frontendData['age_rangeable_type']);
        $this->assertEquals($ageRange->age_rangeable_id, $frontendData['age_rangeable_id']);
        $this->assertEquals($ageRange->from_age, $frontendData['from_age']);
        $this->assertEquals($ageRange->to_age, $frontendData['to_age']);
        $this->assertEquals($ageRange->name->name, $frontendData['name_taxonomy']);
    }
    
    /**
     * @test
     */
    function an_age_range_has_name_translation() {
        list(, $ageRange, $ageRangeEntity) = $this->prepare_models_and_entity();
        $frontendData = $ageRangeEntity->getFrontendData(['taxonomy']);
        
        $this->assertEquals($ageRange->id, $frontendData['id']);
        $this->assertEquals((new TaxonomyEntity($ageRange->name))->getFrontendData(['translations', 'translations_with_plurals']), $frontendData['taxonomy']);
    }
    
}
