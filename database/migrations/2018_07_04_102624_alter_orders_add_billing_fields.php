<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOrdersAddBillingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('billing_country', 2)->nullable()->unsigned();
            $table->string('billing_zip', 255)->nullable()->unsigned();
            $table->string('billing_settlement', 255)->nullable()->unsigned();
            $table->string('billing_address', 255)->nullable()->unsigned();
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
            $table->dropColumn('billing_country');
            $table->dropColumn('billing_zip');
            $table->dropColumn('billing_settlement');
            $table->dropColumn('billing_address');
        });
    }
}
