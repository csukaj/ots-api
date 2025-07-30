<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOrganizationDateRangesToPolymorph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_date_ranges', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'date_rangeable_id');
            $table->string('date_rangeable_type', 255)->default(\App\Organization::class);
        });
        Schema::table('organization_date_ranges', function (Blueprint $table) {
            $table->string('date_rangeable_type', 255)->default(null)->change();
        });
        Schema::rename('organization_date_ranges', 'date_ranges');
        Schema::table('price_elements', function (Blueprint $table) {
            $table->renameColumn('organization_date_range_id', 'date_range_id');
        });
        Schema::table('device_minimum_nights', function (Blueprint $table) {
            $table->renameColumn('organization_date_range_id', 'date_range_id');
        });
        Schema::table('discount_periods', function (Blueprint $table) {
            $table->renameColumn('organization_date_range_id', 'date_range_id');
        });
        Schema::table('organization_meal_plans', function (Blueprint $table) {
            $table->renameColumn('organization_date_range_id', 'date_range_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_meal_plans', function (Blueprint $table) {
            $table->renameColumn('date_range_id', 'organization_date_range_id');
        });
        Schema::table('discount_periods', function (Blueprint $table) {
            $table->renameColumn('date_range_id', 'organization_date_range_id');
        });
        Schema::table('device_minimum_nights', function (Blueprint $table) {
            $table->renameColumn('date_range_id', 'organization_date_range_id');
        });
        Schema::table('price_elements', function (Blueprint $table) {
            $table->renameColumn('date_range_id', 'organization_date_range_id');
        });
        Schema::rename('date_ranges', 'organization_date_ranges');
        Schema::table('organization_date_ranges', function (Blueprint $table) {
            $table->dropColumn(['date_rangeable_id', 'date_rangeable_type']);
            $table->integer('organization_id')->unsigned();

            $table->foreign('organization_id')->references('id')->on('organizations');
        });
    }
}
