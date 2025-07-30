<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsAndPricesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('productable_id')->unsigned();
            $table->string('productable_type', 255);
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();
            $table->double('margin_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('products', function(Blueprint $table) {
            $table->unique(['productable_id', 'productable_type', 'type_taxonomy_id']);
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });
        
        Schema::create('prices', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('name_taxonomy_id')->unsigned()->nullable();
            $table->integer('product_id')->unsigned();
            $table->integer('organization_age_range_id')->unsigned()->nullable();
            $table->integer('amount')->unsigned()->nullable();
            $table->boolean('extra')->default(0);
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();
            $table->double('margin_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('prices', function(Blueprint $table) {
            $table->unique(['product_id', 'organization_age_range_id', 'name_taxonomy_id']);
            $table->foreign('name_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('organization_age_range_id')->references('id')->on('organization_age_ranges');
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });
        
        Schema::create('price_elements', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('price_id')->unsigned()->nullable();
            $table->integer('organization_meal_plan_id')->unsigned()->nullable();
            $table->integer('organization_date_range_id')->unsigned()->nullable();
            $table->double('net_price')->nullable();
            $table->double('rack_price')->nullable();
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();
            $table->double('margin_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('price_elements', function(Blueprint $table) {
            $table->unique(['price_id', 'organization_meal_plan_id', 'organization_date_range_id']);
            $table->foreign('price_id')->references('id')->on('prices');
            $table->foreign('organization_meal_plan_id')->references('id')->on('organization_meal_plans');
            $table->foreign('organization_date_range_id')->references('id')->on('organization_date_ranges');
            
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('prices', function(Blueprint $table) {
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['organization_meal_plan_id']);
            $table->dropForeign(['organization_date_range_id']);
            $table->dropForeign(['organization_age_range_id']);
            $table->dropForeign(['margin_type_taxonomy_id']);
        });
        Schema::drop('prices');
        
        Schema::table('products', function(Blueprint $table) {
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['margin_type_taxonomy_id']);
        });
        Schema::drop('products');
    }
}
