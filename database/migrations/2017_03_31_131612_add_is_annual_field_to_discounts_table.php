<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsAnnualFieldToDiscountsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('discounts', function (Blueprint $table) {
            $table->boolean('is_annual')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn('is_annual');
        });
    }

}
