<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_classifications', function (Blueprint $table) {
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
        
        Schema::create('offer_metas', function (Blueprint $table) {
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
        Schema::table('offer_classifications', function(Blueprint $table) {
            $table->dropForeign(['value_taxonomy_id']);
            $table->dropForeign(['classification_taxonomy_id']);
            $table->dropForeign(['discount_id']);
        });
        Schema::drop('offer_classifications');
        
        Schema::table('offer_metas', function(Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropForeign(['taxonomy_id']);
        });
        Schema::drop('offer_metas');
    }
}
