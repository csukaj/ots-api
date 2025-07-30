<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('name_description_id')->unsigned();
            $table->integer('location_id')->unsigned();
            $table->integer('organization_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('name_description_id')->references('id')->on('descriptions');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('organization_id')->references('id')->on('organizations');
        });

        Schema::create('program_descriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('program_id')->unsigned();
            $table->integer('taxonomy_id')->unsigned();
            $table->integer('description_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')->references('id')->on('programs');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('description_id')->references('id')->on('descriptions');
        });

        DB::statement('ALTER SEQUENCE programs_id_seq RESTART WITH 10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('programs', function(Blueprint $table) {
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['organization_id']);
        });

        Schema::table('program_descriptions', function(Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['description_id']);
        });

        Schema::dropIfExists('programs');
        Schema::dropIfExists('program_descriptions');
    }
}
