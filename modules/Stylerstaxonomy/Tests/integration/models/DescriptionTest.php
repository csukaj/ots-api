<?php

namespace Modules\Stylerstaxonomy\Tests\Integration\Models;

use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionTranslation;
use Tests\TestCase;

class DescriptionTest extends TestCase {
    /**
     * @test
     */
    function it_can_be_created()
    {
        $description = new Description(['description' => $this->faker->paragraph]);
        $this->assertTrue($description->save());
    }
    
    /**
     * @test
     */
    function it_can_be_translated()
    {
        $description = new Description(['description' => $this->faker->paragraph]);
        $this->assertTrue($description->save());
        
        $descriptionParagraph = $this->faker->paragraph;
        
        $translation = new DescriptionTranslation(['description_id' => $description->id, 'description' => $descriptionParagraph]);
        $translation->language_id = Config::get('taxonomies.languages.' . Config::get('taxonomies.default_language'))['id'];
        $this->assertTrue($translation->save());
        
        $this->assertEquals($descriptionParagraph, $description->translations->toArray()[0]['description']);
    }
}
