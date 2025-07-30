<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationGroupPoisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organization_group_pois', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('organization_group_id')->unsigned();
            $table->integer('poi_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('organization_group_id')->references('id')->on('organization_groups');
            $table->foreign('poi_id')->references('id')->on('pois');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_group_pois', function (Blueprint $table) {
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['organization_group_id']);
            $table->dropForeign(['poi_id']);
        });

        Schema::dropIfExists('organization_group_pois');
    }
}
