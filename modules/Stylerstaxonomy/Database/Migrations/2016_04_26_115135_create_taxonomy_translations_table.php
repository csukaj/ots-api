<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @codeCoverageIgnore
 */
class CreateTaxonomyTranslationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taxonomy_translations', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('language_id')->unsigned();
            $table->integer('taxonomy_id')->unsigned();
            $table->string('name', 255);

            $table->timestamps();
            $table->softdeletes();
        });
        
        Schema::table('taxonomy_translations', function(Blueprint $table)
        {
            $table->foreign('language_id')->references('id')->on('languages');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taxonomy_translations', function(Blueprint $table)
        {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['taxonomy_id']);
        });
        
        Schema::drop('taxonomy_translations');
    }

}
