<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @codeCoverageIgnore
 */
class SetCascadeForTaxonomyTranslationPlurals extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taxonomy_translation_plurals', function(Blueprint $table) {
            $table->dropForeign(['taxonomy_translation_id']);
            $table->dropForeign(['type_taxonomy_id']);
        });
        Schema::table('taxonomy_translation_plurals', function(Blueprint $table) {
            $table->foreign('taxonomy_translation_id')->references('id')->on('taxonomy_translations')->onDelete('cascade');
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taxonomy_translation_plurals', function(Blueprint $table) {
            $table->dropForeign(['taxonomy_translation_id']);
            $table->dropForeign(['type_taxonomy_id']);
        });
        Schema::table('taxonomy_translation_plurals', function(Blueprint $table) {
            $table->foreign('taxonomy_translation_id')->references('id')->on('taxonomy_translations');
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

}
