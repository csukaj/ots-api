<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Models;

use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class LanguageTest extends TestCase
{

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
            $this->assertEquals($properties['date_format'], $language->date_format);
            $this->assertEquals($properties['time_format'], $language->time_format);
            $this->assertEquals($properties['first_day_of_week'], $language->first_day_of_week);
        }
    }

    /**
     * @test
     */
    function has_name()
    {
        $language = Language::findOrFail(1);
        $tx = Taxonomy::findOrFail($language->name_taxonomy_id);
        $this->assertEquals($tx->name, $language->name->name);
    }

}
