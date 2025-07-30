<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgramClassificationsAndMetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_classifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('program_id')->unsigned();
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

            $table->foreign('program_id')->references('id')->on('programs');
            $table->foreign('parent_classification_id')->references('id')->on('program_classifications');
            $table->foreign('classification_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('value_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('charge_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });

        Schema::create('program_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('program_id')->unsigned();
            $table->integer('parent_classification_id')->unsigned()->nullable();
            $table->integer('taxonomy_id')->unsigned();
            $table->string('value');
            $table->integer('priority')->unsigned()->nullable();
            $table->integer('additional_description_id')->unsigned()->nullable();
            $table->boolean('is_listable')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('program_id')->references('id')->on('programs');
            $table->foreign('parent_classification_id')->references('id')->on('program_classifications');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('additional_description_id')->references('id')->on('descriptions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('program_classifications', function(Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['charge_taxonomy_id']);
            $table->dropForeign(['value_taxonomy_id']);
            $table->dropForeign(['classification_taxonomy_id']);
            $table->dropForeign(['program_id']);
        });

        Schema::table('program_metas', function(Blueprint $table) {
            $table->dropForeign(['additional_description_id']);
            $table->dropForeign(['program_date_range_id']);
            $table->dropForeign(['taxonomy_id']);
            $table->dropForeign(['program_id']);
        });
    }
}
