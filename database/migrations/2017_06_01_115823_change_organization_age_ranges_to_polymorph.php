<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOrganizationAgeRangesToPolymorph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_age_ranges', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->renameColumn('organization_id', 'age_rangeable_id');
            $table->string('age_rangeable_type', 255)->default(\App\Organization::class);
        });
        Schema::table('organization_age_ranges', function (Blueprint $table) {
            $table->string('age_rangeable_type', 255)->default(null)->change();
        });
        Schema::rename('organization_age_ranges', 'age_ranges');
        Schema::table('device_usage_elements', function (Blueprint $table) {
            $table->renameColumn('organization_age_range_id', 'age_range_id');
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->renameColumn('organization_age_range_id', 'age_range_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->renameColumn('age_range_id', 'organization_age_range_id');
        });
        Schema::table('device_usage_elements', function (Blueprint $table) {
            $table->renameColumn('age_range_id', 'organization_age_range_id');
        });
        Schema::rename('age_ranges', 'organization_age_ranges');
        Schema::table('organization_age_ranges', function (Blueprint $table) {
            $table->dropColumn(['age_rangeable_id', 'age_rangeable_type']);
            $table->integer('organization_id')->unsigned();
        });
    }
}
