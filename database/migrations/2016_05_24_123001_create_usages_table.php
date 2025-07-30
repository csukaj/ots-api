<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::create('device_usages', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->unsigned();
            
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('device_usages', function(Blueprint $table) {
            $table->foreign('device_id')->references('id')->on('devices');
        });
        
        Schema::create('device_usage_elements', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('device_usage_id')->unsigned();
            $table->integer('organization_age_range_id')->unsigned();
            $table->tinyInteger('amount')->unsigned();
            
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('device_usage_elements', function(Blueprint $table) {
            $table->foreign('device_usage_id')->references('id')->on('device_usages');
            $table->foreign('organization_age_range_id')->references('id')->on('organization_age_ranges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_usages', function(Blueprint $table) {
            $table->dropForeign(['device_id']);
        });
        Schema::table('device_usage_elements', function(Blueprint $table) {
            $table->dropForeign(['device_usage_id']);
            $table->dropForeign(['organization_age_range_id']);
        });
        
        Schema::drop('device_usages');
        Schema::drop('device_usage_elements');
    }
}
