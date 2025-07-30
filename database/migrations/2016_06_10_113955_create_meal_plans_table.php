<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal_plans', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('name_taxonomy_id')->unsigned()->nullable();
            $table->tinyInteger('service_bitmap')->unsigned();

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('meal_plans', function(Blueprint $table) {
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
        Schema::table('meal_plans', function(Blueprint $table)
        {
            $table->dropForeign(['name_taxonomy_id']);
        });
        Schema::drop('meal_plans');
    }
}
