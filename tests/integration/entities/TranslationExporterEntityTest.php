<?php

namespace Tests\Integration\Entities;

use App\Entities\TranslationExporterEntity;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TranslationExporterEntityTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_entity() {
        return new TranslationExporterEntity('hu');
    }

    /**
     * @test
     */
    function it_can_generate_csv_file() {
        $translationEntity = $this->prepare_entity();
        $translationEntity->generateCsv();
        $csvFilePath = $translationEntity->getCsvFilePath();
        $this->assertTrue(file_exists($csvFilePath));
        $this->assertTrue(File::size($csvFilePath) > 0);
    }



}
