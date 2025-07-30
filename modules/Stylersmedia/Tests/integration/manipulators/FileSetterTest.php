<?php

namespace Modules\Stylersmedia\Tests\Integration\Manipulators;

use Modules\Stylersmedia\Entities\FileEntity;
use Modules\Stylersmedia\Manipulators\FileSetter;
use Tests\TestCase;

class FileSetterTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    public function it_can_store_a_file() {
        $data = [
            'path' => 'image_xx.jpg',
            'width' => 1024,
            'height' => 768,
            'type' => 'image',
            'description' => ['en' => 'Lorem Ipsum dolor sit amite']
        ];

        $fileSetter = new FileSetter($data);
        $fileEntity = (new FileEntity($fileSetter->set()))->getFrontendData();

        $this->assertEquals('storage/modules/stylersmedia/images/image_xx.jpg', $fileEntity['path']);
        $this->assertEquals($data['width'], $fileEntity['width']);
        $this->assertEquals($data['height'], $fileEntity['height']);
        $this->assertEquals($data['type'], $fileEntity['type']);
        $this->assertEquals($data['description']['en'], $fileEntity['description']['en']);
    }

}
