<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddChargeTaxonomyToDeviceClassifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_classifications', function(Blueprint $table) {
            $table->integer('charge_taxonomy_id')->unsigned()->nullable();
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_classifications', function(Blueprint $table) {
            $table->dropForeign(['charge_taxonomy_id']);
        });
    }
}
