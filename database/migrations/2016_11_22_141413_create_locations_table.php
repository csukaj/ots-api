<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('island_id')->unsigned()->nullable();
            $table->integer('district_id')->unsigned()->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('po_box')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('island_id')->references('id')->on('islands');
            $table->foreign('district_id')->references('id')->on('districts');
        });
        
        Schema::table('organizations', function (Blueprint $table) {
            $table->integer('location_id')->unsigned()->nullable();
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organizations', function(Blueprint $table)
        {
            $table->dropColumn('location_id');
            $table->dropForeign(['location_id']);
        });
        
        Schema::table('locations', function(Blueprint $table)
        {
            $table->dropForeign(['island_id']);
            $table->dropForeign(['district_id']);
        });
        Schema::drop('locations');
    }
}
