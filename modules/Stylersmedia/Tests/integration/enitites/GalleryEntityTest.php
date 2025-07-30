<?php

namespace Modules\Stylersmedia\Tests\Integration\Entities;

use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylersmedia\Entities\GalleryEntity;
use Tests\TestCase;

class GalleryEntityTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    function it_can_retrieve_a_simple_gallery() {

        $gallery = Gallery::first();
        $galleryEntity = new GalleryEntity($gallery);
        $data = $galleryEntity->getFrontendData();

        $this->assertEquals($gallery->id, $data['id']);
        $this->assertEquals($gallery->galleryable_id, $data['galleryable_id']);
        $this->assertEquals($gallery->galleryable_type, $data['galleryable_type']);
        $this->assertEquals(count($gallery->items), count($data['items']));

        $counter = 0;
        foreach ($gallery->items as $galleryItem){
            $this->assertEquals($galleryItem->file_id,$data['items'][$counter]['id']);
            $counter++;
        }
        
    }
}