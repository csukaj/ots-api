<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGalleriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('files', function(Blueprint $table) {
            $table->increments('id');
            $table->string('extension', 20)->nullable();
            $table->string('path', 400)->nullable();
            $table->integer('type_taxonomy_id')->unsigned()->nullable();
            $table->integer('description_id')->unsigned()->nullable();
            $table->integer('width')->unsigned()->nullable();
            $table->integer('height')->unsigned()->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('description_id')->references('id')->on('descriptions');
        });

        Schema::create('galleries', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('galleryable_id')->unsigned();
            $table->string('galleryable_type', 255);
            $table->integer('name_description_id')->unsigned()->nullable();
            $table->integer('role_taxonomy_id')->unsigned()->nullable();
            $table->integer('priority')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('name_description_id')->references('id')->on('descriptions');
            $table->foreign('role_taxonomy_id')->references('id')->on('taxonomies');
        });

        Schema::create('gallery_items', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('gallery_id')->unsigned();
            $table->integer('file_id')->unsigned();
            $table->integer('priority')->unsigned()->nullable();
            $table->boolean('is_highlighted')->default(0);

            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('gallery_id')->references('id')->on('galleries');
            $table->foreign('file_id')->references('id')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('files', function(Blueprint $table) {
            $table->dropForeign(['role_taxonomy_id']);
            $table->dropForeign(['description_id']);
            $table->dropForeign(['type_taxonomy_id']);
        });
        Schema::table('galleries', function(Blueprint $table) {
            $table->dropForeign(['name_description_id']);
        });
        Schema::table('gallery_items', function(Blueprint $table) {
            $table->dropForeign(['gallery_id']);
            $table->dropForeign(['file_id']);
        });

        Schema::drop('files');
        Schema::drop('galleries');
        Schema::drop('gallery_items');
    }

}
