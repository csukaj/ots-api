<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceMinimumNightsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('device_minimum_nights', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->unsigned();
            $table->integer('organization_date_range_id')->unsigned();
            $table->tinyInteger('minimum_nights')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('device_minimum_nights', function (Blueprint $table) {
            $table->foreign('device_id')->references('id')->on('devices');
            $table->foreign('organization_date_range_id')->references('id')->on('organization_date_ranges');
            $table->unique(['device_id', 'organization_date_range_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('device_minimum_nights', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->dropForeign(['organization_date_range_id']);
        });
        Schema::drop('device_minimum_nights');
    }

}
