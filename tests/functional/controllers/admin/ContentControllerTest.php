<?php

namespace Tests\Functional\Controllers\Admin;

use App\Content;
use App\Entities\ContentEntity;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class ContentControllerTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;

    private function prepare_models_and_entity() {
        $content = Content::all()->first();
        return [$content, (new ContentEntity($content))];
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_contents() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/content', 'GET', $token, [], true);

        $allContent = ContentEntity::getCollection(Content::all());

        $this->assertEquals(count($allContent), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allContent[$i];
            $this->assertEquals($expected, $actual);
        }
        
        $expectedCategories = Taxonomy::find(Config::get('taxonomies.content_category'))->getChildren()->pluck('name')->toArray();
        sort($expectedCategories);
        sort($responseData['categories']);
        
        $this->assertEquals($expectedCategories, $responseData['categories']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_a_content() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        list($content, $contentEntity) = $this->prepare_models_and_entity();

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/content/{$content->id}", 'GET', $token, [], true);

        $frontendData = $contentEntity->getFrontendData();
        $data = $responseData['data'];
        $this->assertEquals($frontendData, $data);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_a_new_content() {
        list($token, $user) = $this->login([Config::get('stylersauth.role_admin')]);


        $data = [
            "title" => [
                "en" => $this->faker->word, "hu" => $this->faker->word, "de" => $this->faker->word, "ru" => $this->faker->word
            ],
            "author" => $user->name,
            "status" => "draft",
            "lead" => [
                "en" => $this->faker->sentence, "hu" => $this->faker->sentence, "de" => $this->faker->sentence, "ru" => $this->faker->sentence
            ],
            "content" => [
                "en" => $this->faker->text, "hu" => $this->faker->text, "de" => $this->faker->text, "ru" => $this->faker->text
            ],
            "url" => [
                "en" => "about-us777",
                "hu" => "rolunk777"
            ],
            "meta_title" => [
                "en" => $this->faker->word, "hu" => $this->faker->word, "de" => $this->faker->word, "ru" => $this->faker->word
            ],
            "meta_description" => [
                "en" => $this->faker->sentence, "hu" => $this->faker->sentence, "de" => $this->faker->sentence, "ru" => $this->faker->sentence
            ],
            "meta_keyword" => [
                "en" => $this->faker->word, "hu" => $this->faker->word, "de" => $this->faker->word, "ru" => $this->faker->word
            ]
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/content', 'POST', $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($data['title']['en'], $responseData->data->title->en);
        $this->assertEquals($data['author'], $responseData->data->author);
        $this->assertEquals($data['status'], $responseData->data->status);
        $this->assertEquals($data['lead']['en'], $responseData->data->lead->en);
        $this->assertEquals($data['content']['en'], $responseData->data->content->en);
        $this->assertEquals($data['url']['en'], $responseData->data->url->en);
        $this->assertEquals($data['meta_title']['en'], $responseData->data->meta_title->en);
        $this->assertEquals($data['meta_description']['en'], $responseData->data->meta_description->en);
        $this->assertEquals($data['meta_keyword']['en'], $responseData->data->meta_keyword->en);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_edit_a_content() {
        list($token, $user) = $this->login([Config::get('stylersauth.role_admin')]);
        list($content, ) = $this->prepare_models_and_entity();

        $data = [
            "id" => $content->id,
            "title" => [
                "en" => $this->faker->word, "hu" => $this->faker->word, "de" => $this->faker->word, "ru" => $this->faker->word
            ],
            "author" => $user->name,
            "status" => "draft",
            "lead" => [
                "en" => $this->faker->sentence, "hu" => $this->faker->sentence, "de" => $this->faker->sentence, "ru" => $this->faker->sentence
            ],
            "content" => [
                "en" => $this->faker->text, "hu" => $this->faker->text, "de" => $this->faker->text, "ru" => $this->faker->text
            ],
            "url" => [
                "en" => "about-us123",
                "hu" => "rolunk123"
            ]
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/content/{$content->id}", 'PUT', $token, $data);

        $this->assertEquals($content->id, $responseData->data->id);
        $this->assertEquals($data['title']['en'], $responseData->data->title->en);
        $this->assertEquals($data['author'], $responseData->data->author);
        $this->assertEquals($data['status'], $responseData->data->status);
        $this->assertEquals($data['lead']['en'], $responseData->data->lead->en);
        $this->assertEquals($data['content']['en'], $responseData->data->content->en);
        $this->assertEquals($data['url']['en'], $responseData->data->url->en);
        $this->assertEmpty($responseData->data->meta_title);
        $this->assertEmpty($responseData->data->meta_description);
        $this->assertEmpty($responseData->data->meta_keyword);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_delete_a_content() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        list($content,) = $this->prepare_models_and_entity();

        $this->assertSuccessfulHttpApiRequest("/admin/content/{$content->id}", 'DELETE', $token);

        $this->assertEmpty(Content::find($content->id));
        $this->assertNotEmpty(Content::withTrashed()->find($content->id));
    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_pages() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/content/pages', 'GET', $token, [], true);

        $allContent = ContentEntity::getCollection(Content::page()->get());

        $this->assertEquals(count($allContent), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allContent[$i];
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_posts() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/content/posts', 'GET', $token, [], true);

        $allContent = ContentEntity::getCollection(Content::post()->get());

        $this->assertEquals(count($allContent), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allContent[$i];
            $this->assertEquals($expected, $actual);
        }
    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_posts_of_type() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/content/posts-of-category/256', 'GET', $token, [], true);

        $allContent = ContentEntity::getCollection(Content::post()->ofCategory(256)->get());

        $this->assertEquals(count($allContent), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allContent[$i];
            $this->assertEquals($expected, $actual);
        }
    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_a_new_content_with_category() {
        list($token, $user) = $this->login([Config::get('stylersauth.role_admin')]);


        $data = [
            "title" => [
                "en" => $this->faker->word, "hu" => $this->faker->word, "de" => $this->faker->word, "ru" => $this->faker->word
            ],
            "author" => $user->name,
            "status" => "draft",
            "category" => "Category1",
            "lead" => [
                "en" => $this->faker->sentence, "hu" => $this->faker->sentence, "de" => $this->faker->sentence, "ru" => $this->faker->sentence
            ],
            "content" => [
                "en" => $this->faker->text, "hu" => $this->faker->text, "de" => $this->faker->text, "ru" => $this->faker->text
            ],
            "url" => [
                "en" => "about-us777",
                "hu" => "rolunk777"
            ],
            "meta_title" => [
                "en" => $this->faker->word, "hu" => $this->faker->word, "de" => $this->faker->word, "ru" => $this->faker->word
            ],
            "meta_description" => [
                "en" => $this->faker->sentence, "hu" => $this->faker->sentence, "de" => $this->faker->sentence, "ru" => $this->faker->sentence
            ],
            "meta_keyword" => [
                "en" => $this->faker->word, "hu" => $this->faker->word, "de" => $this->faker->word, "ru" => $this->faker->word
            ]
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/content', 'POST', $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($data['title']['en'], $responseData->data->title->en);
        $this->assertEquals($data['author'], $responseData->data->author);
        $this->assertEquals($data['status'], $responseData->data->status);
        $this->assertEquals($data['lead']['en'], $responseData->data->lead->en);
        $this->assertEquals($data['content']['en'], $responseData->data->content->en);
        $this->assertEquals($data['url']['en'], $responseData->data->url->en);
        $this->assertEquals($data['meta_title']['en'], $responseData->data->meta_title->en);
        $this->assertEquals($data['meta_description']['en'], $responseData->data->meta_description->en);
        $this->assertEquals($data['meta_keyword']['en'], $responseData->data->meta_keyword->en);
    }
}
