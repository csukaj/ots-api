<?php

namespace Modules\Stylersmedia\Tests\Integration\Entities;

use Illuminate\Support\Facades\Config;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Entities\FileEntity;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;
use Tests\TestCase;

class FileEntityTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    function it_can_retrieve_a_single_file() {
        $file = new File([
            'path' => 'image_xx.jpg',
            'width' => 1024,
            'height' => 768,
            'type_taxonomy_id' => Taxonomy::getTaxonomy('image', Config::get('taxonomies.file_type'))->id,
            'description_id' => (new DescriptionSetter(['en' => 'Lorem Ipsum']))->set()->id
        ]);
        $file->saveOrFail();
        $frontendData = (new FileEntity($file))->getFrontendData();

        $this->assertEquals($file->id, $frontendData['id']);
        $this->assertEquals('storage/modules/stylersmedia/images/image_xx.jpg', $frontendData['path']);
        $this->assertEquals($file->width, $frontendData['width']);
        $this->assertEquals($file->height, $frontendData['height']);
        $this->assertEquals('image', $frontendData['type']);
        $this->assertEquals((new DescriptionEntity($file->description))->getFrontendData()['en'], $frontendData['description']['en']);
    }

}
