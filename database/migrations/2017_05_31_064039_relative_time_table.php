<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RelativeTimeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relative_times', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('day')->unsigned();
            $table->integer('precision_taxonomy_id')->unsigned();
            $table->integer('time_of_day_taxonomy_id')->unsigned()->nullable();
            $table->time('time')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('precision_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('time_of_day_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('relative_times', function (Blueprint $table) {
            $table->dropForeign(['precision_taxonomy_id']);
            $table->dropForeign(['time_of_day_taxonomy_id']);
        });
        Schema::drop('relative_times');
    }
}
