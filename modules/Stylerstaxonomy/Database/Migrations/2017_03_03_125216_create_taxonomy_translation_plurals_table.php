<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @codeCoverageIgnore
 */
class CreateTaxonomyTranslationPluralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taxonomy_translation_plurals', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('taxonomy_translation_id')->unsigned();
            $table->integer('type_taxonomy_id')->unsigned();
            $table->string('name', 255);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('taxonomy_translation_id')->references('id')->on('taxonomy_translations');
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
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
        
        Schema::drop('taxonomy_translation_plurals');
    }
}
