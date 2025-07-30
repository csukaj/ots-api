<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Models;

use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class ContactTest extends TestCase {
    
    /**
     * @test
     */
    function matches_constants()
    {
        foreach (Config::get('taxonomies.languages') as $name => $properties) {
            $tx = Taxonomy::find($properties['id']);
            $this->assertEquals($name, $tx->name);
            
            $language = Language::where(['name_taxonomy_id' => $tx->id])->first();
            $this->assertEquals($properties['iso_code'], $language->iso_code);
        }
    }
    
}
