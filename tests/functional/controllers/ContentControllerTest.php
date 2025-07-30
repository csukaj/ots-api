<?php

namespace Tests\Functional\Controllers;

use App\Content;
use App\Entities\ContentEntity;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class ContentControllerTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    public function it_can_get_a_content() {
        $content = Content::published()->get()->first();
        $contentEntity = new ContentEntity($content);

        $responseData = $this->assertSuccessfulHttpApiRequest("/content/{$content->id}", 'GET', [], [], true);
        $frontendData = $contentEntity->getFrontendData(['frontend']);
        $data = $responseData['data'];
        $this->assertEquals($frontendData, $data);
    }

    /**
     * @test
     */
    public function it_can_list_pages() {
        $responseData = $this->assertSuccessfulHttpApiRequest('/content/pages', 'GET', [], [], true);

        $allContent = ContentEntity::getCollection(Content::published()->page()->get(), ['frontend']);

        $this->assertEquals(count($allContent), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allContent[$i];
            $this->assertEquals($expected, $actual);
        }

        $expectedCategories = Taxonomy::find(Config::get('taxonomies.content_category'))->getChildren()->keyBy('id')->map(function($item) {
                    return $item->name;
                })->toArray();
        ksort($expectedCategories);
        ksort($responseData['categories']);

        $this->assertEquals($expectedCategories, $responseData['categories']);
    }

    /**
     * @test
     */
    public function it_can_list_posts() {
        $responseData = $this->assertSuccessfulHttpApiRequest('/content/posts', 'GET', [], [], true);

        $allContent = ContentEntity::getCollection(Content::published()->post()->get(), ['frontend']);

        $this->assertEquals(count($allContent), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allContent[$i];
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @test
     */
    public function it_can_list_posts_of_type() {
        $responseData = $this->assertSuccessfulHttpApiRequest('/content/posts-of-category/256', 'GET', [], [], true);

        $allContent = ContentEntity::getCollection(Content::published()->post()->ofCategory(256)->get(), ['frontend']);

        $this->assertEquals(count($allContent), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allContent[$i];
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @test
     */
    public function it_can_not_list_drafts() {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function it_can_not_show_draft_content() {
        $content = Content::where('status_taxonomy_id', '!=', Config::get('taxonomies.content_statuses.published'))->first();

        
        list(, $responseData, $response) = $this->httpApiRequest("/content/{$content->id}", 'GET', [], [], true);
        $response->assertStatus(404);
        $this->assertFalse($responseData['success']);
    }

    /**
     * @test
     */
    public function it_can_get_a_content_by_url() {
        $content = Content::published()->get()->first();
        $contentEntity = new ContentEntity($content);

        $rqdata = ['url' => $content->url->description];
        $responseData = $this->assertSuccessfulHttpApiRequest("/content/by-url", 'POST', [], $rqdata, true);
        $frontendData = $contentEntity->getFrontendData(['frontend']);
        $data = $responseData['data'];
        $this->assertEquals($frontendData, $data);
    }
    
    /**
     * @test
     */
    public function it_can_not_show_draft_content_by_url() {
        $content = Content::where('status_taxonomy_id', '!=', Config::get('taxonomies.content_statuses.published'))->first();

        $rqdata = ['url' => $content->url->description];
        list(, $responseData,$response) = $this->httpApiRequest("/content/by-url", 'POST', [], $rqdata, true);
        $response->assertStatus(404);
        $this->assertFalse($responseData['success']);
    }

}
