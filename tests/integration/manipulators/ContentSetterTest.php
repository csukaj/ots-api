<?php

namespace Tests\Integration\Manipulators;

use App\Content;
use App\ContentMedia;
use App\ContentModification;
use App\Entities\ContentEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Manipulators\ContentSetter;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Entities\FileEntity;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;
use Tests\TestCase;

class ContentSetterTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    function it_can_save_content() {
        $data = [
            'title' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'author' => 'manager',
            'status' => 'draft',
            'written_by' => 'Famous Writer',
            'edited_by' => 'Famous Editor',
            'lead' => [
                'en' => $this->faker->sentence, 'hu' => $this->faker->sentence, 'de' => $this->faker->sentence, 'ru' => $this->faker->sentence
            ],
            'content' => [
                'en' => $this->faker->text, 'hu' => $this->faker->text, 'de' => $this->faker->text, 'ru' => $this->faker->text
            ],
            'url' => [
                'en' => 'about-us777',
                'hu' => 'rolunk777'
            ],
            'meta_title' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'meta_description' => [
                'en' => $this->faker->sentence, 'hu' => $this->faker->sentence, 'de' => $this->faker->sentence, 'ru' => $this->faker->sentence
            ],
            'meta_keyword' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ]
        ];

        $cSetter = new ContentSetter($data);
        $content = $cSetter->set();
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($data['title']['en'], $content->title->description);
        $this->assertEquals($data['author'], $content->author->name);
        $this->assertEquals($data['status'], $content->status->name);
        $this->assertEquals($data['lead']['en'], $content->lead->description);
        $this->assertEquals($data['content']['en'], $content->content->description);
        $this->assertEquals($data['url']['en'], $content->url->description);
        $this->assertEquals($data['meta_title']['en'], $content->metaTitle->description);
        $this->assertEquals($data['meta_description']['en'], $content->metaDescription->description);
        $this->assertEquals($data['meta_keyword']['en'], $content->metaKeyword->description);
        $this->assertEquals($data['written_by'], $content->written_by);
        $this->assertEquals($data['edited_by'], $content->edited_by);
    }

    /**
     * @test
     */
    function it_cant_save_content_with_invalid_data() {

        $data = [
            'title' => [
                'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'author' => 'noneuser',
            'status' => 'notexisting'
        ];

        $this->expectException(UserException::class);
        (new ContentSetter($data))->set();
    }

    /**
     * @test
     */
    function it_can_update_content() {
        $data = [
            'title' => [
                'en' => $this->faker->word . rand(0, 100), 'hu' => $this->faker->word . rand(0, 100), 'de' => $this->faker->word . rand(0, 100), 'ru' => $this->faker->word . rand(0, 100)
            ],
            'author' => 'manager',
            'status' => 'draft',
            'lead' => [
                'en' => $this->faker->sentence, 'hu' => $this->faker->sentence, 'de' => $this->faker->sentence, 'ru' => $this->faker->sentence
            ],
            'content' => [
                'en' => $this->faker->text, 'hu' => $this->faker->text, 'de' => $this->faker->text, 'ru' => $this->faker->text
            ],
            'url' => [
                'en' => 'about-us888',
                'hu' => 'rolunk888'
            ],
            'meta_title' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'meta_description' => [
                'en' => $this->faker->sentence, 'hu' => $this->faker->sentence, 'de' => $this->faker->sentence, 'ru' => $this->faker->sentence
            ],
            'meta_keyword' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'written_by' => 'Famous Writer',
            'edited_by' => 'Famous Editor'
        ];

        $content = (new ContentSetter($data))->set();
        $this->assertInstanceOf(Content::class, $content);


        $update = [
            'id' => $content->id,
            'title' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'author' => 'root',
            'status' => 'published',
            'lead' => [
                'en' => $this->faker->sentence, 'hu' => $this->faker->sentence, 'de' => $this->faker->sentence, 'ru' => $this->faker->sentence
            ],
            'content' => [
                'en' => $this->faker->text, 'hu' => $this->faker->text, 'de' => $this->faker->text, 'ru' => $this->faker->text
            ],
            'url' => [
                'en' => 'about-us999',
                'hu' => 'rolunk999'
            ],
            'written_by' => 'Edited Famous Writer',
            'edited_by' => 'Edited Famous Editor'
        ];

        $updatedContent = (new ContentSetter($update))->set();
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($content->id, $updatedContent->id);
        $this->assertEquals($update['title']['en'], $updatedContent->title->description);
        $this->assertEquals($update['author'], $updatedContent->author->name);
        $this->assertEquals($update['status'], $updatedContent->status->name);
        $this->assertEquals($update['lead']['en'], $updatedContent->lead->description);
        $this->assertEquals($update['content']['en'], $updatedContent->content->description);
        $this->assertEquals($update['url']['en'], $updatedContent->url->description);
        $this->assertEquals($update['written_by'], $updatedContent->written_by);
        $this->assertEquals($update['edited_by'], $updatedContent->edited_by);
    }

    /**
     * @test
     */
    function it_can_save_content_with_category() {
        $data = [
            'title' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'author' => 'manager',
            'status' => 'draft',
            'category' => 'Category1',
            'content' => [
                'en' => $this->faker->text, 'hu' => $this->faker->text, 'de' => $this->faker->text, 'ru' => $this->faker->text
            ],
            'url' => [
                'en' => 'about-us-blogpost',
                'hu' => 'rolunk777-blogpost'
            ]
        ];

        $cSetter = new ContentSetter($data);
        $content = $cSetter->set();
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($data['title']['en'], $content->title->description);
        $this->assertEquals($data['author'], $content->author->name);
        $this->assertEquals($data['status'], $content->status->name);
        $this->assertEquals($data['category'], $content->category->name);
        $this->assertEquals($data['content']['en'], $content->content->description);
        $this->assertEquals($data['url']['en'], $content->url->description);
    }

    /**
     * @test
     */
    function it_can_log_modification_on_model_change() {

        $countBefore = ContentModification::count();

        $data = [
            'title' => ['en' => $this->faker->word, 'hu' => $this->faker->word],
            'author' => 'manager',
            'status' => 'draft',
            'content' => ['en' => $this->faker->text, 'hu' => $this->faker->text],
            'url' => ['en' => 'about-us777', 'hu' => 'rolunk777']
        ];

        $cSetter = new ContentSetter($data);
        $content = $cSetter->set();
        $contentEntity = new ContentEntity($content);

        $countAfter = ContentModification::count();
        $this->assertEquals($countBefore + 1, $countAfter);

        $modification = $content->modifications()->get()->last();

        $this->assertEquals($content->id, $modification->content_id);
        $this->assertEquals($content->author->id, $modification->editor_user_id);
        $this->assertEquals(\json_encode($contentEntity->getFrontendData()), $modification->new_content);
    }

    /**
     * @test
     */
    function it_can_save_lead_image() {
        $data = [
            'title' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'author' => 'manager',
            'status' => 'draft',
            'written_by' => 'Famous Writer',
            'edited_by' => 'Famous Editor',
            'lead_image' => (new FileEntity(File::all()->first()))->getFrontendData(),
            'url' => ['en' => 'about-us777', 'hu' => 'rolunk777']
        ];

        $cSetter = new ContentSetter($data);
        $content = $cSetter->set();
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($data['lead_image']['id'], $content->leadImages->first()->mediable_id);
        $this->assertEquals($data['written_by'], $content->written_by);
        $this->assertEquals($data['edited_by'], $content->edited_by);
    }

    /**
     * @test
     */
    function it_can_extract_image_ids_from_description() {
        $fileIds = File::limit(5)->orderBy('id')->get()->pluck('id')->toArray();
        $filePaths = File::limit(5)->orderBy('id')->get()->pluck('path');
        $text = '';
        foreach ($filePaths as $path) {
            $text .= $this->faker->sentence . "<img src=\"/{$path}\" alt=\"{$this->faker->word}\" />";
        }
        $description = (new DescriptionSetter(['en' => $text, 'hu' => $text]))->set();

        $imageIds = ContentSetter::getImageIdsFromDescription($description);
        $this->assertEquals($fileIds, $imageIds);
    }
    
    /**
     * @test
     */
    function it_can_save_content_with_image() {
        $imgFile = File::first();
        $imgTag = "<img alt=\"{$this->faker->word}\"  src=\"/{$imgFile->path}\">";
        
        $data = [
            'title' => [
                'en' => $this->faker->word, 'hu' => $this->faker->word, 'de' => $this->faker->word, 'ru' => $this->faker->word
            ],
            'author' => 'manager',
            'status' => 'draft',
            'written_by' => 'Famous Writer',
            'edited_by' => 'Famous Editor',
            'lead' => [
                'en' => $this->faker->sentence, 'hu' => $this->faker->sentence, 'de' => $this->faker->sentence, 'ru' => $this->faker->sentence
            ],
            'content' => [
                'en' => $this->faker->text. $imgTag, 'hu' => $this->faker->text. $imgTag, 'de' => $this->faker->text, 'ru' => $this->faker->text
            ],
            'url' => [
                'en' => 'about-us7773',
                'hu' => 'rolunk7773'
            ]
        ];
        

        $content = (new ContentSetter($data))->set();
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($data['content']['en'], $content->content->description);

        $contentMedia = ContentMedia
                ::where('content_id', $content->id)
                ->where('media_role_taxonomy_id', '=', Config::get('taxonomies.media_roles.content_image'))
                ->get();
        $this->assertCount(1, $contentMedia);
        $this->assertEquals($imgFile->id, $contentMedia->first()->mediable_id);
    }

}
