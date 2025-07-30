<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Models;

use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyTranslation;
use Tests\TestCase;

class TaxonomyTranslationTest extends TestCase {
    
    /**
     * @test
     */
    function tx_can_be_translated()
    {
        $tx = new Taxonomy();
        $tx->name = $this->faker->word;
        $this->assertTrue($tx->save());
        
        $name = $this->faker->word;
        
        $translation = new TaxonomyTranslation(['taxonomy_id' => $tx->id, 'name' => $name]);
        $translation->language_id = Config::get('taxonomies.languages.' . Config::get('taxonomies.default_language'))['id'];
        $this->assertTrue($translation->save());
        
        $this->assertEquals($name, $tx->translations->toArray()[0]['name']);
    }
}
