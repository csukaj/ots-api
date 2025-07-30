<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('orders', function(Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('nationality', 2);
            $table->string('email', 255);
            $table->string('telephone', 255);
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->integer('device_id')->unsigned();
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('amount')->unsigned();
            $table->integer('meal_plan_id')->unsigned();
            $table->integer('room_index')->unsigned();
            $table->float('price');
            $table->text('json');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('device_id')->references('id')->on('devices');
            $table->foreign('meal_plan_id')->references('id')->on('meal_plans');
        });

        Schema::create('order_item_guests', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('order_item_id')->unsigned();
            $table->integer('guest_index')->unsigned();
            $table->integer('age_range_id')->unsigned();
            $table->string('first_name', 255);
            $table->string('last_name', 255);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_item_id')->references('id')->on('order_items');
            $table->foreign('age_range_id')->references('id')->on('organization_age_ranges');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('order_item_guests', function(Blueprint $table) {
            $table->dropForeign(['order_item_id']);
            $table->dropForeign(['age_range_id']);
        });
        Schema::drop('order_item_guests');

        Schema::table('order_items', function(Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['device_id']);
            $table->dropForeign(['meal_plan_id']);
        });
        Schema::drop('order_items');

        Schema::drop('orders');
    }
}