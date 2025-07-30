<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrderItemsToPolymorph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::table('price_elements', function (Blueprint $table) {
            $table->dropForeign(['organization_meal_plan_id']);
        });*/
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['device_id']);
            $table->renameColumn('device_id', 'order_itemable_id');
            $table->string('order_itemable_type', 255)->default(\App\Device::class);
            $table->renameColumn('room_index', 'order_itemable_index');
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('order_itemable_type', 255)->default(null)->change();
        });

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
