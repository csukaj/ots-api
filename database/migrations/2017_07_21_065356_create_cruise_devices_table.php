<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCruiseDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cruise_devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cruise_id')->unsigned();
            $table->integer('device_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cruise_id')->references('id')->on('cruises');
            $table->foreign('device_id')->references('id')->on('devices');
        });
        
        Schema::table('organization_groups', function(Blueprint $table) {
            $table->double('margin_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_groups', function(Blueprint $table) {
            $table->dropColumn('margin_value');
        });
        Schema::table('cruise_devices', function(Blueprint $table) {
            $table->dropForeign(['cruise_id']);
            $table->dropForeign(['device_id']);
        });
        Schema::dropIfExists('cruise_devices');
    }
}
