<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OrganizationAgeRangesAddBannedAndFree extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_age_ranges', function (Blueprint $table) {
            $table->boolean('banned')->after('name_taxonomy_id')->default(0);
            $table->boolean('free')->after('name_taxonomy_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_age_ranges', function (Blueprint $table) {
            $table->dropColumn('banned');
            $table->dropColumn('free');
        });
    }
}
