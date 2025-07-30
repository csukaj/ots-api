<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Entities;

use App\Facades\Config;
use App\Organization;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class TaxonomyEntityTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ALWAYS;
    
    /**
     * @test
     */
    function it_can_retrieve_attributes() {
        $tx = Taxonomy::findOrFail(Config::getOrFail('taxonomies.age_ranges.adult.id'));
        $taxonomyEn = (new TaxonomyEntity($tx))->getFrontendData();
        $this->assertEquals([
                'id' => $tx->id,
                'parent_id' => $tx->parent_id,
                'name' => $tx->name,
                'priority' => $tx->priority,
                'is_active' => $tx->is_active,
                'is_required' => $tx->is_required,
                'is_readonly' => $tx->is_readonly,
                'is_merchantable' => $tx->is_merchantable,
                'is_searchable' => $tx->is_searchable,
                'type' => $tx->type,
                'icon' => $tx->icon
            ], $taxonomyEn);
        
        $txEntity = (new TaxonomyEntity($tx))->getFrontendData(['attributes']);
        $this->assertEquals($tx->attributesToArray(), $txEntity);
    }
    
    /**
     * @test
     */
    function it_can_retrieve_translations() {
        $txId = Config::getOrFail('taxonomies.age_ranges.adult.id');
        $taxonomyEn = (new TaxonomyEntity(Taxonomy::findOrFail($txId)))->getFrontendData(['translations']);
        $this->markTestIncomplete();
    }
    
    /**
     * @test
     */
    function it_can_retrieve_translations_with_plurals() {
        $txId = Config::getOrFail('taxonomies.age_ranges.adult.id');
        $taxonomyEn = (new TaxonomyEntity(Taxonomy::findOrFail($txId)))->getFrontendData(['translations_with_plurals']);
        $this->markTestIncomplete();
    }
    
    /**
     * @test
     */
    function it_can_retrieve_relations() {
        $txId = Config::getOrFail('taxonomies.price_modifier_application_levels.room_request.price_modifier_condition_types.long_stay.metas.restricted_to_device_ids.id');
        $organization = Organization::firstOrFail();
        $taxonomyEn = (new TaxonomyEntity(Taxonomy::findOrFail($txId)))->getFrontendData(['descendants', 'relation'], [$organization]);
        $this->assertTrue(array_key_exists('relation', $taxonomyEn));
    }
    
    /**
     * @test
     */
    function it_can_retrieve_searchable_info() {
        $tx = Taxonomy::first();
        $taxonomyEn = (new TaxonomyEntity($tx))->getFrontendData(['searchable_info']);
        $this->assertEquals($tx->name, $taxonomyEn['name']['en']);
        $this->assertEquals($tx->priority, $taxonomyEn['priority']);
    }
}