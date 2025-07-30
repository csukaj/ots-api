<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pois', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('name_description_id')->unsigned();
            $table->integer('description_description_id')->unsigned()->nullable();
            $table->integer('location_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('name_description_id')->references('id')->on('descriptions');
            $table->foreign('description_description_id')->references('id')->on('descriptions');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pois', function (Blueprint $table) {
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['description_description_id']);
            $table->dropForeign(['location_id']);
        });

        Schema::dropIfExists('pois');
    }
}
