<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgramRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned();
            $table->integer('child_id')->unsigned();
            $table->integer('sequence')->unsigned();
            $table->integer('relative_time_id')->unsigned();

            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('programs');
            $table->foreign('child_id')->references('id')->on('programs');
            $table->foreign('relative_time_id')->references('id')->on('relative_times');
            $table->unique(['parent_id', 'child_id']);
        });

        DB::statement('ALTER SEQUENCE program_relations_id_seq RESTART WITH 10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('program_relations', function(Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['child_id']);
            $table->dropForeign(['relative_time_id']);
        });

        Schema::dropIfExists('program_relations');
    }
}
