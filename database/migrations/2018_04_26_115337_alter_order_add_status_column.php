<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrderAddStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            //$table->integer('status_taxonomy_id')->default(525)->nullable()->unsigned();
            $table->integer('status_taxonomy_id')->nullable()->unsigned();
            $table->foreign('status_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['status_taxonomy_id']);
            $table->dropColumn('status_taxonomy_id');
        });
    }
}
