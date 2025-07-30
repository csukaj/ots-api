<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIslandsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('islands', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('name_taxonomy_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('name_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('islands', function(Blueprint $table) {
            $table->dropForeign(['name_taxonomy_id']);
        });
        Schema::drop('islands');
    }

}
