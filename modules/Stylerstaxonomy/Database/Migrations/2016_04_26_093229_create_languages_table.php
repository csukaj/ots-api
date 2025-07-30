<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
/**
 * @codeCoverageIgnore
 */
class CreateLanguagesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('languages', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('name_taxonomy_id')->unsigned();
            $table->string('iso_code', 255)->unique();
            $table->string('date_format', 255);
            $table->string('time_format', 255);
            $table->string('first_day_of_week', 255);
            $table->boolean('is_default')->default(0);
            
            $table->timestamps();
            $table->softdeletes();
        });
        
        Schema::table('languages', function(Blueprint $table)
        {
            $table->foreign('name_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('languages', function(Blueprint $table)
        {
            $table->dropForeign(['taxonomy_id']);
        });
        
        Schema::drop('languages');
    }

}
