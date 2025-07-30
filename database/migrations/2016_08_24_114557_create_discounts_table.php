<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('name_description_id')->unsigned();
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('offer_taxonomy_id')->unsigned();
            $table->integer('description_description_id')->unsigned()->nullable();
            $table->integer('priority')->unsigned()->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('name_description_id')->references('id')->on('descriptions');
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('offer_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('description_description_id')->references('id')->on('descriptions');
        });
        
        Schema::create('discount_combinations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('first_discount_id')->unsigned();
            $table->integer('second_discount_id')->unsigned();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('first_discount_id')->references('id')->on('discounts');
            $table->foreign('second_discount_id')->references('id')->on('discounts');
        });
        
        Schema::create('discount_periods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('discount_id')->unsigned();
            $table->integer('organization_date_range_id')->unsigned();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('organization_date_range_id')->references('id')->on('organization_date_ranges');
        });
        
        Schema::create('discount_classifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('discount_id')->unsigned();
            $table->integer('classification_taxonomy_id')->unsigned();
            $table->integer('value_taxonomy_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('classification_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('value_taxonomy_id')->references('id')->on('taxonomies');
        });
        
        Schema::create('discount_metas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('discount_id')->unsigned();
            $table->integer('taxonomy_id')->unsigned();
            $table->string('value')->unsigned();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('discount_classifications', function(Blueprint $table) {
            $table->dropForeign(['value_taxonomy_id']);
            $table->dropForeign(['classification_taxonomy_id']);
            $table->dropForeign(['discount_id']);
        });
        Schema::drop('discount_classifications');
        
        Schema::table('discount_metas', function(Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropForeign(['taxonomy_id']);
        });
        Schema::drop('discount_metas');
        
        Schema::table('discount_case_relations', function(Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropForeign(['discount_case_id']);
        });
        Schema::drop('discount_case_relations');
        
        Schema::table('discount_cases', function(Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });
        Schema::drop('discount_cases');

        Schema::table('discount_periods', function(Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropForeign(['organization_date_range_id']);
        });
        Schema::drop('discount_periods');
        
        Schema::table('discounts', function(Blueprint $table) {
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['offer_taxonomy_id']);
            $table->dropForeign(['description_description_id']);
        });
        Schema::drop('discounts');
    }
}
