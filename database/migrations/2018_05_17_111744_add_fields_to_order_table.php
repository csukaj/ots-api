<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('status_log')->nullable();
            $table->string('token', 255)->nullable();
            $table->timestamp('token_created_at')->nullable();
            $table->text('local_storage')->nullable();
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
            $table->dropColumn('status_log');
            $table->dropColumn('token');
            $table->dropColumn('token_created_at');
            $table->dropColumn('local_storage');
        });
    }
}
