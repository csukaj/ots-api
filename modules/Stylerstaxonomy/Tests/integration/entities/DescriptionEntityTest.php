<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Entities;

use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\DescriptionTranslation;
use Modules\Stylerstaxonomy\Entities\Language;
use Tests\TestCase;

class DescriptionEntityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ALWAYS;
    
    /**
     * @test
     */
    function it_can_retrieve_a_single_description() {
        $description = new Description(['description' => $descriptionDescription = $this->faker->word]);
        $description->saveOrFail();
        
        $data = (new DescriptionEntity($description))->getFrontendData();
        
        $this->assertEquals(1, count($data));
        $this->assertEquals($descriptionDescription, $data['en']);
    }
    
    /**
     * @test
     */
    function it_can_retrieve_a_single_description_with_translations() {
        $languages = Language::all();
        $translationDescriptions = [];
        
        $description = new Description(['description' => $translationDescriptions['en'] = $this->faker->word]);
        $description->saveOrFail();
        
        foreach ($languages as $language) {
            $translation = new DescriptionTranslation([
                'description_id' => $description->id,
                'language_id' => $language->id,
                'description' => $translationDescriptions[$language->iso_code] = $this->faker->word
            ]);
            $translation->saveOrFail();
        }
        
        $data = (new DescriptionEntity($description))->getFrontendData();
        
        $this->assertEquals(count($languages), count($data));
        foreach ($translationDescriptions as $languageCode => $translationDescription) {
            $this->assertEquals($translationDescription, $data[$languageCode]);
        }
    }
    
    /**
     * @test
     */
    function it_can_retrieve_multiple_descriptions() {
        $description1 = new Description(['description' => $descriptionDescription1 = $this->faker->word]);
        $description1->saveOrFail();
        
        $description2 = new Description(['description' => $descriptionDescription2 = $this->faker->word]);
        $description2->saveOrFail();
        
        $data = DescriptionEntity::getCollection([$description1, $description2]);
        
        $this->assertEquals(2, count($data));
        $this->assertEquals($descriptionDescription1, $data[0]['en']);
        $this->assertEquals($descriptionDescription2, $data[1]['en']);
    }
}