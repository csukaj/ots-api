<?php
namespace Tests\Integration\Database;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaxonomyIntegrityText extends TestCase {
    static public $setupMode = self::SETUPMODE_ONCE;
    
    /**
     * @test
     */
    public function there_are_no_duplicates() {
        $duplicates = DB::select(
            'SELECT * FROM view_taxonomy_duplicates'
        );
        $this->assertEmpty($duplicates);
    }

    /**
     * @test
     */
    public function there_is_no_difference_in_taxonomy_name_and_english_translation() {
        $difference = DB::select(
            'SELECT * FROM taxonomies
  JOIN taxonomy_translations ON taxonomies.id = taxonomy_translations.taxonomy_id
WHERE language_id = 1 AND taxonomy_translations.name != taxonomies.name'
        );
        $this->assertEmpty($difference);
    }

    /**
     * @test
     */
    public function there_is_english_translation_for_every_other_language_translation() {
        $difference = DB::select(
            'SELECT DISTINCT taxonomy_id FROM taxonomy_translations WHERE language_id != 1
EXCEPT
SELECT DISTINCT taxonomy_id FROM taxonomy_translations WHERE language_id = 1'
        );
        $this->assertEmpty($difference);
    }
}
