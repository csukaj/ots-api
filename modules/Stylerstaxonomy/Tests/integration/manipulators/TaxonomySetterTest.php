<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Manipulators;

use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\TaxonomySetter;
use Tests\TestCase;

class TaxonomySetterTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    public function it_can_create_taxonomy() {
        $taxonomy = (new TaxonomySetter(['en' => $word = $this->faker->word]))->set();

        $this->assertTrue(!!$taxonomy->id);
        $this->assertEquals($word, $taxonomy->name);
        $this->assertEquals(1, count($taxonomy->translations));
    }

    /**
     * @test
     */
    public function it_can_update_taxonomy() {
        $taxonomy1 = new Taxonomy(['name' => $this->faker->word]);
        $taxonomy1->saveOrFail();
        $taxonomy2 = (new TaxonomySetter(['en' => $word = $this->faker->word], $taxonomy1->id))->set();

        $this->assertEquals($taxonomy1->id, $taxonomy2->id);
        $this->assertEquals($word, $taxonomy2->name);
        $this->assertCount(1, $taxonomy2->translations);
    }

    /**
     * @test
     */
    public function it_can_restore_taxonomy() {
        $taxonomy1 = new Taxonomy(['name' => $word = $this->faker->word]);
        $taxonomy1->saveOrFail();
        
        Taxonomy::destroy($taxonomy1->id);
        
        
        $taxonomy2 = (new TaxonomySetter(['en' => $word]))->set();

        $this->assertEquals($taxonomy1->id, $taxonomy2->id);
        $this->assertEquals($word, $taxonomy2->name);
        $this->assertCount(1, $taxonomy2->translations);
        
        
    }

    /**
     * @test
     */
    function it_can_create_a_new_taxonomy_with_translations() {
        $words = [];
        $taxonomy = (new TaxonomySetter([
            'en' => $words['en'] = $this->faker->word,
            'de' => $words['de'] = $this->faker->word,
            'hu' => $words['hu'] = $this->faker->word,
            'ru' => $words['ru'] = $this->faker->word
                ]))->set();

        $this->assertTrue(!!$taxonomy->id);
        $this->assertEquals($words['en'], $taxonomy->name);

        $translations = $taxonomy->translations;
        $this->assertCount(4, $taxonomy->translations);
        foreach ($translations as $translation) {
            $this->assertEquals($words[$translation->language->iso_code], $translation->name);
        }
    }

    /**
     * @test
     */
    function it_can_update_existing_translations() {
        $taxonomy1 = (new TaxonomySetter([
            'en' => $this->faker->word,
            'de' => $this->faker->word,
            'hu' => $this->faker->word,
            'ru' => $this->faker->word
                ]))->set();

        $words2 = [];
        $taxonomy2 = (new TaxonomySetter([
            'en' => $words2['en'] = $this->faker->word,
            'de' => $words2['de'] = $this->faker->word,
            'hu' => $words2['hu'] = $this->faker->word,
            'ru' => $words2['ru'] = $this->faker->word
                ], $taxonomy1->id))->set();

        $this->assertEquals($taxonomy1->id, $taxonomy2->id);
        $this->assertEquals($words2['en'], $taxonomy2->name);

        $translations = $taxonomy2->translations;
        $this->assertCount(4, $taxonomy2->translations);
        foreach ($translations as $translation) {
            $this->assertEquals($words2[$translation->language->iso_code], $translation->name);
        }
    }

    /**
     * @test
     */
    function it_can_create_a_new_taxonomy_with_parent() {
        $parent = 1;
        $taxonomy = (new TaxonomySetter(['en' => $word = $this->faker->word], null, $parent))->set();

        $this->assertTrue(!!$taxonomy->id);
        $this->assertEquals($parent, $taxonomy->parent_id);
        $this->assertEquals($word, $taxonomy->name);
        $this->assertCount(1, $taxonomy->translations);
    }
    
    /**
     * @test
     */
    function it_can_create_a_new_taxonomy_with_type() {
        $taxonomy = (new TaxonomySetter(['en' => $word = $this->faker->word], null, null, \App\Facades\Config::getOrFail('stylerstaxonomy.type_meta')))->set();

        $this->assertTrue(!!$taxonomy->id);
        $this->assertEquals($word, $taxonomy->name);
        $this->assertCount(1, $taxonomy->translations);
    }

}
