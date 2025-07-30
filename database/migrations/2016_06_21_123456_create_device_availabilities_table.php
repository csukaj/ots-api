<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_availabilities', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->unsigned();
            $table->datetime('from_time')->nullable();
            $table->datetime('to_time')->nullable();
            $table->smallInteger('amount')->unsigned();
            $table->timestamps();
            $table->index(['from_time', 'to_time']);
        });
        Schema::table('device_availabilities', function(Blueprint $table) {
            $table->foreign('device_id')->references('id')->on('devices');
        });
        
        Schema::table('devices', function(Blueprint $table) {
            $table->integer('name_taxonomy_id')->unsigned()->nullable();
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
        Schema::table('devices', function(Blueprint $table) {
            $table->dropForeign(['name_taxonomy_id']);
            $table->dropColumn('name_taxonomy_id');
        });
        
        Schema::table('device_availabilities', function(Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropIndex(['from_time', 'to_time']);
        });
        Schema::drop('device_availabilities');
    }
}
