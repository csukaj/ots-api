<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @codeCoverageIgnore
 */
class CreateDescriptionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('descriptions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->text('description');

            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('description_translations', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('description_id')->unsigned();
            $table->integer('language_id')->unsigned();
            $table->text('description');

            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::table('description_translations', function(Blueprint $table)
        {
            $table->foreign('description_id')->references('id')->on('descriptions');
            $table->foreign('language_id')->references('id')->on('languages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('description_translations', function(Blueprint $table)
        {
            $table->dropForeign(['description_id']);
            $table->dropForeign(['language_id']);
        });
        
        Schema::drop('descriptions');
        Schema::drop('description_translations');
    }

}
