<?php

namespace Tests\Integration\Entities;

use App\Content;
use App\Entities\ContentEntity;
use App\Entities\ContentModificationEntity;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class ContentEntityTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity() {
        $content = Content::all()->first();
        return [$content, (new ContentEntity($content))];
    }

    private function getDescTranslations(Description $description) {
        return (new DescriptionEntity($description))->getFrontendData();
    }

    /**
     * @test
     */
    function it_can_present_content_data() {
        list($content, $contentEntity) = $this->prepare_model_and_entity();
        /** @var ContentEntity $contentEntity */
        $frontendData = $contentEntity->getFrontendData();

        $this->assertEquals($content->id, $frontendData['id']);
        $this->assertEquals($this->getDescTranslations($content->title), $frontendData['title']);
        $this->assertEquals($content->author->name, $frontendData['author']);
        $this->assertEquals($content->written_by, $frontendData['written_by']);
        $this->assertEquals($content->edited_by, $frontendData['edited_by']);
        $this->assertEquals($content->status->name, $frontendData['status']);
        $this->assertEquals($this->getDescTranslations($content->lead), $frontendData['lead']);
        $this->assertEquals($this->getDescTranslations($content->content), $frontendData['content']);
        $this->assertEquals($this->getDescTranslations($content->url), $frontendData['url']);
        $this->assertEquals($this->getDescTranslations($content->metaTitle), $frontendData['meta_title']);
        $this->assertEquals($this->getDescTranslations($content->metaDescription), $frontendData['meta_description']);
        $this->assertEquals($this->getDescTranslations($content->metaKeyword), $frontendData['meta_keyword']);
        $this->assertEquals(count($content->modifications), count($frontendData['modifications']));
        foreach ($content->modifications as $idx => $value) {
            $this->assertEquals((new ContentModificationEntity($value))->getFrontendData(), $frontendData['modifications'][$idx]);
        }
        $this->assertNotEmpty($frontendData['created_at']);
    }

    /**
     * @test
     */
    function it_can_present_content_data_for_frontend() {
        list($content, $contentEntity) = $this->prepare_model_and_entity();
        /** @var ContentEntity $contentEntity */
        $frontendData = $contentEntity->getFrontendData(['frontend']);

        $this->assertEquals($content->id, $frontendData['id']);
        $this->assertEquals($this->getDescTranslations($content->title), $frontendData['title']);
        $this->assertEquals($content->author->name, $frontendData['author']);
        $this->assertArrayNotHasKey('status', $frontendData);
        $this->assertArrayHasKey('category', $frontendData);
        $this->assertEquals($this->getDescTranslations($content->lead), $frontendData['lead']);
        $this->assertEquals($this->getDescTranslations($content->content), $frontendData['content']);
        $this->assertEquals($this->getDescTranslations($content->url), $frontendData['url']);
        $this->assertEquals($this->getDescTranslations($content->metaTitle), $frontendData['meta_title']);
        $this->assertEquals($this->getDescTranslations($content->metaDescription), $frontendData['meta_description']);
        $this->assertEquals($this->getDescTranslations($content->metaKeyword), $frontendData['meta_keyword']);
        $this->assertNotEmpty($frontendData['lead_image']);
        $this->assertNotEmpty($frontendData['created_at']);
        $this->assertNotEmpty($frontendData['updated_at']);
    }
    
    /**
     * @test
     */
    function it_can_present_content_of_type_page() {
        $content = Content::page()->first();
        $frontendData = (new ContentEntity($content))->getFrontendData();

        $this->assertEquals($content->id, $frontendData['id']);
        $this->assertNull($frontendData['category']);
    }

    /**
     * @test
     */
    function it_can_present_content_of_type_post() {
        $content = Content::post()->first();
        $frontendData = (new ContentEntity($content))->getFrontendData();

        $this->assertEquals($content->id, $frontendData['id']);
        $this->assertNotNull($frontendData['category']);
        $this->assertEquals($content->category->name, $frontendData['category']);
    }

}
