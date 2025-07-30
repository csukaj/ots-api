<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinimumNightsColumnToOrganizationDateRanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_date_ranges', function (Blueprint $table) {
            $table->tinyInteger('minimum_nights')->after('margin_value')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_date_ranges', function (Blueprint $table) {
            $table->dropColumn('minimum_nights');
        });
    }
}
