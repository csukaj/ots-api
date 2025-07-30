<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cruise_id')->unsigned();
            $table->datetime('from_time');
            $table->datetime('to_time');
            $table->integer('frequency_taxonomy_id')->unsigned();
            $table->integer('relative_time_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cruise_id')->references('id')->on('cruises');
            $table->foreign('frequency_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('relative_time_id')->references('id')->on('relative_times');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cruises', function (Blueprint $table) {
            $table->dropForeign(['cruise_id']);
            $table->dropForeign(['frequency_taxonomy_id']);
            $table->dropForeign(['relative_time_id']);
        });

        Schema::dropIfExists('schedules');
    }
}
