<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationDateRangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_date_ranges', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('name_description_id')->unsigned()->nullable();
            $table->integer('organization_id')->unsigned();
            $table->datetime('from_time');
            $table->datetime('to_time');
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();
            $table->double('margin_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('organization_date_ranges', function(Blueprint $table) {
            $table->foreign('name_description_id')->references('id')->on('descriptions');
            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });
        
        Schema::table('organization_meal_plans', function(Blueprint $table) {
            $table->integer('organization_date_range_id')->unsigned();
            $table->foreign('organization_date_range_id')->references('id')->on('organization_date_ranges');
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
            $table->dropForeign(['organization_date_range_id']);
        });
        
        Schema::table('organization_date_ranges', function(Blueprint $table) {
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['margin_type_taxonomy_id']);
        });
        Schema::drop('organization_date_ranges');
    }
}
