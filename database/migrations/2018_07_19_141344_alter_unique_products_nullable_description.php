<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUniqueProductsNullableDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unique_products', function(Blueprint $table) {
            $table->text('description')->nullable(true)->change();
            $table->text('from_date')->nullable(true)->change();
            $table->text('to_date')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unique_products', function(Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->text('from_date')->nullable(true)->change();
            $table->text('to_date')->nullable(true)->change();
        });
    }
}
