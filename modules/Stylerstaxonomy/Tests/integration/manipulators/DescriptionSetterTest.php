<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Manipulators;

use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;
use Tests\TestCase;

class DescriptionSetterTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ALWAYS;
    
    /**
     * @test
     */
    function it_can_create_a_new_description() {
        $description = (new DescriptionSetter(['en' => $sentence = $this->faker->sentence]))->set();
        
        $this->assertTrue(!!$description->id);
        $this->assertEquals($sentence, $description->description);
        $this->assertEquals(0, count($description->translations));
    }
    
    /**
     * @test
     */
    function it_can_update_an_existing_description() {
        $description1 = new Description(['description' => $this->faker->sentence]);
        $description1->saveOrFail();
        $description2 = (new DescriptionSetter(['en' => $sentence = $this->faker->sentence], $description1->id))->set();
        
        $this->assertEquals($description1->id, $description2->id);
        $this->assertEquals($sentence, $description2->description);
        $this->assertEquals(0, count($description2->translations));
    }
    
    /**
     * @test
     */
    function it_can_create_a_new_description_with_translations() {
        $sentences = [];
        $description = (new DescriptionSetter([
            'en' => $sentences['en'] = $this->faker->sentence,
            'de' => $sentences['de'] = $this->faker->sentence,
            'hu' => $sentences['hu'] = $this->faker->sentence,
            'ru' => $sentences['ru'] = $this->faker->sentence
        ]))->set();
        
        $this->assertTrue(!!$description->id);
        $this->assertEquals($sentences['en'], $description->description);
        
        $translations = $description->translations;
        $this->assertEquals(3, count($translations));
        foreach ($translations as $translation) {
            $this->assertEquals($sentences[$translation->language->iso_code], $translation->description);
        }
    }
    
    /**
     * @test
     */
    function it_can_update_existing_translations() {
        $description1 = (new DescriptionSetter([
            'en' => $this->faker->sentence,
            'de' => $this->faker->sentence,
            'hu' => $this->faker->sentence,
            'ru' => $this->faker->sentence
        ]))->set();
        
        $sentences2 = [];
        $description2 = (new DescriptionSetter([
            'en' => $sentences2['en'] = $this->faker->sentence,
            'de' => $sentences2['de'] = $this->faker->sentence,
            'hu' => $sentences2['hu'] = $this->faker->sentence,
            'ru' => $sentences2['ru'] = $this->faker->sentence
        ], $description1->id))->set();
        
        $this->assertEquals($description1->id, $description2->id);
        $this->assertEquals($sentences2['en'], $description2->description);
        
        $translations = $description2->translations;
        $this->assertEquals(3, count($translations));
        foreach ($translations as $translation) {
            $this->assertEquals($sentences2[$translation->language->iso_code], $translation->description);
        }
    }
    
    /**
     * @test
     */
    function it_can_create_a_new_translation_in_existing_description() {
        $description1 = (new DescriptionSetter([
            'en' => $this->faker->sentence,
            'de' => $this->faker->sentence
        ]))->set();
        
        $description2 = (new DescriptionSetter([
            'en' => $this->faker->sentence,
            'de' => $this->faker->sentence,
            'hu' => $sentence = $this->faker->sentence
        ], $description1->id))->set();
        
        $this->assertEquals($description1->id, $description2->id);
        $this->assertEquals(2, count($description2->translations));
        $this->assertEquals($sentence, $description2->translations[1]->description);
    }
    
    /**
     * @test
     */
    function it_can_remove_an_existing_translation_from_a_description() {
        $description1 = (new DescriptionSetter([
            'en' => $this->faker->sentence,
            'de' => $this->faker->sentence,
            'hu' => $this->faker->sentence
        ]))->set();
        
        $description2 = (new DescriptionSetter([
            'en' => $this->faker->sentence,
            'de' => $sentence = $this->faker->sentence
        ], $description1->id))->set();
        
        $this->assertEquals($description1->id, $description2->id);
        $this->assertEquals(1, count($description2->translations));
        $this->assertEquals($sentence, $description2->translations[0]->description);
    }
}