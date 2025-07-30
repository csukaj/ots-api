<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganizationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('name_description_id')->unsigned();
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('category_taxonomy_id')->unsigned();
            $table->boolean('is_active')->default(0);
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('pricing_logic_taxonomy_id')->unsigned()->nullable();
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('name_description_id')->references('id')->on('descriptions');
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('category_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('parent_id')->references('id')->on('organizations');
            $table->foreign('pricing_logic_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });
        
        Illuminate\Support\Facades\DB::statement('ALTER SEQUENCE organizations_id_seq RESTART WITH 10000');

        Schema::create('organization_classifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->unsigned();
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

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('parent_classification_id')->references('id')->on('organization_classifications');
            $table->foreign('classification_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('value_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('charge_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('organization_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->unsigned();
            $table->integer('parent_classification_id')->unsigned()->nullable();
            $table->integer('taxonomy_id')->unsigned();
            $table->string('value');
            $table->integer('priority')->unsigned()->nullable();
            $table->integer('additional_description_id')->unsigned()->nullable();
            $table->boolean('is_listable')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('parent_classification_id')->references('id')->on('organization_classifications');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('organization_descriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->unsigned();
            $table->integer('taxonomy_id')->unsigned();
            $table->integer('description_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('description_id')->references('id')->on('descriptions');
        });
        
        Schema::create('organization_age_ranges', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('from_age')->unsigned()->default(0);
            $table->tinyInteger('to_age')->unsigned()->nullable();
            $table->integer('organization_id')->unsigned();
            $table->integer('name_taxonomy_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('name_taxonomy_id')->references('id')->on('taxonomies');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('organizations', function(Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['category_taxonomy_id']);
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['pricing_logic_taxonomy_id']);
            $table->dropForeign(['margin_type_taxonomy_id']);
        });

        Schema::table('organization_classifications', function(Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['charge_taxonomy_id']);
            $table->dropForeign(['value_taxonomy_id']);
            $table->dropForeign(['classification_taxonomy_id']);
            $table->dropForeign(['organization_id']);
        });

        Schema::table('organization_metas', function(Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['organization_date_range_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['organization_id']);
        });

        Schema::table('organization_descriptions', function(Blueprint $table) {
            $table->dropForeign(['description_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['organization_id']);
        });
        
        Schema::table('organization_age_ranges', function(Blueprint $table) {
            $table->dropForeign(['name_taxonomy_id']);
            $table->dropForeign(['organization_id']);
        });

        Schema::drop('organizations');
        Schema::drop('organization_classifications');
        Schema::drop('organization_metas');
        Schema::drop('organization_descriptions');
        Schema::drop('organization_age_ranges');
    }

}
