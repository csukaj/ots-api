<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationMealPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_meal_plans', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->unsigned()->nullable();
            $table->integer('meal_plan_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('organization_meal_plans', function(Blueprint $table) {
            $table->foreign('meal_plan_id')->references('id')->on('meal_plans');
            $table->foreign('organization_id')->references('id')->on('organizations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_meal_plans', function(Blueprint $table) {
            $table->dropForeign(['meal_plan_id']);
            $table->dropForeign(['organization_id']);
        });
        Schema::drop('organization_meal_plans');
    }
}
