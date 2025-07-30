<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUniqueProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unique_products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_id');
            $table->unsignedInteger('cart_id');
            $table->string('name');
            $table->string('unit');
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->integer('amount');
            $table->float('net_price');
            $table->float('margin');
            $table->float('tax');
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cart_id')->references('id')->on('carts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('unique_products');
    }
}
