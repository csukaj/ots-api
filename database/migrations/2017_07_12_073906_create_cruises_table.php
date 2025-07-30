<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCruisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cruises', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('name_description_id')->unsigned();
            $table->integer('location_id')->unsigned()->nullable();
            $table->boolean('is_active')->default(0);
            $table->integer('ship_company_id')->unsigned()->nullable();
            $table->integer('ship_group_id')->unsigned()->nullable();
            $table->integer('itinerary_id')->unsigned()->nullable();
            $table->integer('pricing_logic_taxonomy_id')->unsigned()->nullable();
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('name_description_id')->references('id')->on('descriptions');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('ship_company_id')->references('id')->on('organizations');
            $table->foreign('ship_group_id')->references('id')->on('organization_groups');
            $table->foreign('itinerary_id')->references('id')->on('programs');
            $table->foreign('pricing_logic_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });

        Schema::create('cruise_classifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cruise_id')->unsigned();
            $table->integer('parent_classification_id')->unsigned()->nullable();
            $table->integer('classification_taxonomy_id')->unsigned();
            $table->integer('value_taxonomy_id')->unsigned()->nullable();
            $table->integer('priority')->unsigned()->nullable();
            $table->integer('charge_taxonomy_id')->unsigned()->nullable();
            $table->integer('additional_description_id')->unsigned()->nullable();
            $table->boolean('is_highlighted')->default(0);
            $table->boolean('is_listable')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cruise_id')->references('id')->on('cruises');
            $table->foreign('parent_classification_id')->references('id')->on('cruise_classifications');
            $table->foreign('classification_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('value_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('charge_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('cruise_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cruise_id')->unsigned();
            $table->integer('parent_classification_id')->unsigned()->nullable();
            $table->integer('taxonomy_id')->unsigned();
            $table->string('value');
            $table->integer('priority')->unsigned()->nullable();
            $table->integer('additional_description_id')->unsigned()->nullable();
            $table->boolean('is_listable')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cruise_id')->references('id')->on('cruises');
            $table->foreign('parent_classification_id')->references('id')->on('cruise_classifications');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('cruise_descriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cruise_id')->unsigned();
            $table->integer('taxonomy_id')->unsigned();
            $table->integer('description_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cruise_id')->references('id')->on('cruises');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('description_id')->references('id')->on('descriptions');
        });

        DB::statement('ALTER SEQUENCE cruises_id_seq RESTART WITH 10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cruises', function (Blueprint $table) {
            $table->dropForeign(['ship_company_id']);
            $table->dropForeign(['ship_group_id']);
            $table->dropForeign(['itinerary_id']);
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['pricing_logic_taxonomy_id']);
            $table->dropForeign(['margin_type_taxonomy_id']);
        });

        Schema::table('cruise_classifications', function (Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['charge_taxonomy_id']);
            $table->dropForeign(['value_taxonomy_id']);
            $table->dropForeign(['classification_taxonomy_id']);
            $table->dropForeign(['cruise_id']);
            $table->dropForeign(['parent_classification_id']);
        });

        Schema::table('cruise_metas', function (Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['cruise_id']);
            $table->dropForeign(['parent_classification_id']);
        });

        Schema::table('cruise_descriptions', function (Blueprint $table) {
            $table->dropForeign(['description_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['cruise_id']);
        });

        Schema::drop('cruises');
        Schema::drop('cruise_classifications');
        Schema::drop('cruise_metas');
        Schema::drop('cruise_descriptions');
    }

}
