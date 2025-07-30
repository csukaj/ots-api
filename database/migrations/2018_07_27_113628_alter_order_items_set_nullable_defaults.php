<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrderItemsSetNullableDefaults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table
                ->string('from_date')
                ->nullable()
                ->change()
            ;
            $table
                ->string('to_date')
                ->nullable()
                ->change()
            ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table
                ->string('from_date')
                ->nullable(false)
                ->change()
            ;
            $table
                ->string('to_date')
                ->nullable(false)
                ->change()
            ;
        });
    }
}
