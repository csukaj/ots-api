<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organization_id')->unsigned();
            $table->integer('amount')->nullable();
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();
            $table->double('margin_value')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations');
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });

        Schema::create('device_classifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->unsigned();
            $table->integer('parent_classification_id')->unsigned()->nullable();
            $table->integer('classification_taxonomy_id')->unsigned();
            $table->integer('value_taxonomy_id')->unsigned()->nullable();
            $table->integer('priority')->unsigned()->nullable();
            $table->integer('price_taxonomy_id')->unsigned()->nullable();
            $table->integer('additional_description_id')->unsigned()->nullable();
            $table->boolean('is_highlighted')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices');
            $table->foreign('parent_classification_id')->references('id')->on('device_classifications');
            $table->foreign('classification_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('value_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('price_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('device_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->unsigned();
            $table->integer('taxonomy_id')->unsigned();
            $table->string('value');
            $table->integer('additional_description_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('device_descriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id')->unsigned();
            $table->integer('taxonomy_id')->unsigned();
            $table->integer('description_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('device_id')->references('id')->on('devices');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('description_id')->references('id')->on('descriptions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('devices', function(Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['margin_type_taxonomy_id']);
        });

        Schema::table('device_classifications', function(Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['price_taxonomy_id']);
            $table->dropForeign(['value_taxonomy_id']);
            $table->dropForeign(['classification_taxonomy_id']);
            $table->dropForeign(['parent_classification_id']);
            $table->dropForeign(['device_id']);
        });

        Schema::table('device_metas', function(Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['device_id']);
        });

        Schema::table('device_descriptions', function(Blueprint $table) {
            $table->dropForeign(['description_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['device_id']);
        });

        Schema::drop('devices');
        Schema::drop('device_classifications');
        Schema::drop('device_metas');
        Schema::drop('device_descriptions');
    }

}
