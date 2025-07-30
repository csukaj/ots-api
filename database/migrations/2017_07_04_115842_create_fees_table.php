<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('age_range_id')->unsigned()->nullable();
            $table->double('net_price')->nullable();
            $table->double('rack_price')->nullable();
            $table->integer('margin_type_taxonomy_id')->unsigned()->nullable();
            $table->double('margin_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::table('fees', function(Blueprint $table) {
            $table->unique(['product_id', 'age_range_id']);
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('age_range_id')->references('id')->on('age_ranges');
            $table->foreign('margin_type_taxonomy_id')->references('id')->on('taxonomies');
        });

        DB::statement('ALTER SEQUENCE fees_id_seq RESTART WITH 10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fees', function(Blueprint $table) {
            $table->dropForeign(['name_description_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['age_range_id']);
            $table->dropForeign(['margin_type_taxonomy_id']);
        });
        Schema::dropIfExists('fees');
    }
}
