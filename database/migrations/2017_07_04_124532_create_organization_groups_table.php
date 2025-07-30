<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationGroupsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('organization_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('name_description_id')->unsigned();
            $table->integer('type_taxonomy_id')->unsigned();
            $table->boolean('is_active')->default(0);
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('pricing_logic_taxonomy_id')->unsigned()->nullable();
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('name_description_id')->references('id')->on('descriptions');
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('parent_id')->references('id')->on('organizations');
            $table->foreign('pricing_logic_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });

        Illuminate\Support\Facades\DB::statement('ALTER SEQUENCE organizations_id_seq RESTART WITH 10000');

        Schema::create('organization_group_classifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_group_id')->unsigned();
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

            $table->foreign('organization_group_id')->references('id')->on('organization_groups');
            $table->foreign('parent_classification_id')->references('id')->on('organization_group_classifications');
            $table->foreign('classification_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('value_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('charge_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('organization_group_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_group_id')->unsigned();
            $table->integer('parent_classification_id')->unsigned()->nullable();
            $table->integer('taxonomy_id')->unsigned();
            $table->string('value');
            $table->integer('priority')->unsigned()->nullable();
            $table->integer('additional_description_id')->unsigned()->nullable();
            $table->boolean('is_listable')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_group_id')->references('id')->on('organization_groups');
            $table->foreign('parent_classification_id')->references('id')->on('organization_group_classifications');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('organization_group_descriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_group_id')->unsigned();
            $table->integer('taxonomy_id')->unsigned();
            $table->integer('description_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_group_id')->references('id')->on('organization_groups');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('description_id')->references('id')->on('descriptions');
        });

        DB::statement('ALTER SEQUENCE organization_groups_id_seq RESTART WITH 10000');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('organization_groups', function(Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['pricing_logic_taxonomy_id']);
            $table->dropForeign(['margin_type_taxonomy_id']);
        });

        Schema::table('organization_group_classifications', function(Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['charge_taxonomy_id']);
            $table->dropForeign(['value_taxonomy_id']);
            $table->dropForeign(['classification_taxonomy_id']);
            $table->dropForeign(['organization_group_id']);
            $table->dropForeign(['parent_classification_id']);
        });

        Schema::table('organization_group_metas', function(Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['organization_group_id']);
            $table->dropForeign(['parent_classification_id']);
        });

        Schema::table('organization_group_descriptions', function(Blueprint $table) {
            $table->dropForeign(['description_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['organization_group_id']);
        });

        Schema::drop('organization_groups');
        Schema::drop('organization_group_classifications');
        Schema::drop('organization_group_metas');
        Schema::drop('organization_group_descriptions');
    }

}
