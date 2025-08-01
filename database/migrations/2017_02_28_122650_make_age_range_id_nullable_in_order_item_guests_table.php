<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAgeRangeIdNullableInOrderItemGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_item_guests', function(Blueprint $table) {
            $table->integer('age_range_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_item_guests', function(Blueprint $table) {
            $table->integer('age_range_id')->unsigned()->change();
        });
    }
}
