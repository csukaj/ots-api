<?php

namespace Tests\Integration\Entities;

use App\Entities\TranslationImporterEntity;
use App\Facades\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\TestCase;

class TranslationImporterEntityTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_entity() {
        $fakeUploadedFile = $this->createUploadedFile(
            "\"general.loading\",\"Loading...\",\"Loooooo\"
            \"general.and\",\"and\",\"and\"
            \"general.readMore\",\"Read more\",\"Read more\"", 'translations_en_2017.csv', 'text/csv', null);
        return new TranslationImporterEntity($fakeUploadedFile);
    }

    private function createUploadedFile($content, $originalName, $mimeType, $error)
    {
        $path = tempnam(sys_get_temp_dir(), uniqid());
        file_put_contents($path, $content);
        return new UploadedFile($path, $originalName, $mimeType, filesize($path), $error, true);
    }


    /**
     * @test
     */
    function it_can_generate_new_translations() {
        $translationImporterEntity = $this->prepare_entity();
        $translationImporterEntity->generateNewTranslations();
        $newTranslationFilePath = base_path() . '/' . Config::get('cache.frontend_i18n_directory').'/en.json';
        $this->assertTrue(file_exists($newTranslationFilePath));
        $newContent = json_decode(\file_get_contents($newTranslationFilePath),true);
        $expectedContent = [
            'general' => [
                'loading' => 'Loooooo',
                'and' => 'and',
                'readMore' => 'Read more'
            ]
        ];
        $this->assertEquals($expectedContent,$newContent);
    }

}