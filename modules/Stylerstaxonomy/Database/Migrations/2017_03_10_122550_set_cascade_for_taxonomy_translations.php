<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @codeCoverageIgnore
 */
class SetCascadeForTaxonomyTranslations extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('taxonomy_translations', function(Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['taxonomy_id']);
        });
        Schema::table('taxonomy_translations', function(Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taxonomy_translations', function(Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['taxonomy_id']);
        });
        Schema::table('taxonomy_translations', function(Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
        });
    }

}
