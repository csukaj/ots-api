<?php

namespace Tests\Integration\Entities;

use App\ContentModification;
use App\Entities\ContentModificationEntity;
use Tests\TestCase;

class ContentModificationEntityTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $content = ContentModification::all()->first();
        return [$content, (new ContentModificationEntity($content))];
    }

    /**
     * @test
     */
    function it_can_present_content_data() {
        list($contentModification, $contentModificationEntity) = $this->prepare_model_and_entity();
        $frontendData = $contentModificationEntity->getFrontendData();
        $this->assertEquals($contentModification->id, $frontendData['id']);
        $this->assertEquals($contentModification->content_id, $frontendData['content_id']);
        $this->assertEquals($contentModification->editor->name, $frontendData['editor']);
        $this->assertEquals($contentModification->created_at, $frontendData['modification']);
    }

}
