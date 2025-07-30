<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeAvailabilitiesToPolymorph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_availabilities', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->renameColumn('device_id', 'available_id');
            $table->string('available_type', 255)->default('App\\Device');
        });
        Schema::table('device_availabilities', function (Blueprint $table) {
            $table->string('available_type', 255)->default(null)->change();
        });
        Schema::rename('device_availabilities', 'availabilities');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
