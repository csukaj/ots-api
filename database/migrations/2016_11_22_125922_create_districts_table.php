<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDistrictsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('districts', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('name_taxonomy_id')->unsigned();
            $table->integer('island_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('name_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('island_id')->references('id')->on('islands');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('districts', function(Blueprint $table) {
            $table->dropForeign(['name_taxonomy_id']);
            $table->dropForeign(['island_id']);
        });
        Schema::drop('districts');
    }

}
