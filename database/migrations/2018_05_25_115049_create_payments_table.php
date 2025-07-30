<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table)
        {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->integer('supplier_id')->unsigned()->nullable();
            $table->string('request_id');
            $table->string('payment_order_id');
            $table->integer('parent_id')->unsigned()->nullable();

            $table->text('status_log')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table)
        {
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('payments');
    }
}
