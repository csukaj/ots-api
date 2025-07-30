<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('title_description_id')->unsigned();
            $table->integer('author_user_id')->unsigned();
            $table->integer('status_taxonomy_id')->unsigned();
            $table->integer('lead_description_id')->unsigned()->nullable();
            $table->integer('content_description_id')->unsigned()->nullable();
            $table->integer('url_description_id')->unsigned();
            $table->integer('meta_title_description_id')->unsigned()->nullable();
            $table->integer('meta_description_description_id')->unsigned()->nullable();
            $table->integer('meta_keyword_description_id')->unsigned()->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('title_description_id')->references('id')->on('descriptions');
            $table->foreign('lead_description_id')->references('id')->on('descriptions');
            $table->foreign('content_description_id')->references('id')->on('descriptions');
            $table->foreign('url_description_id')->references('id')->on('descriptions');
            $table->foreign('meta_title_description_id')->references('id')->on('descriptions');
            $table->foreign('meta_description_description_id')->references('id')->on('descriptions');
            $table->foreign('meta_keyword_description_id')->references('id')->on('descriptions');
            $table->foreign('author_user_id')->references('id')->on('users');
            $table->foreign('status_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contents', function(Blueprint $table) {
            $table->dropForeign(['title_description_id']);
            $table->dropForeign(['lead_description_id']);
            $table->dropForeign(['content_description_id']);
            $table->dropForeign(['url_description_id']);
            $table->dropForeign(['meta_title_description_id']);
            $table->dropForeign(['meta_description_description_id']);
            $table->dropForeign(['meta_keyword_description_id']);
            $table->dropForeign(['author_user_id']);
            $table->dropForeign(['status_taxonomy_id']);
            
        });
        
        Schema::drop('contents');
    }
}
