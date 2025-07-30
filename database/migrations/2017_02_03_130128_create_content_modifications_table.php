<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentModificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_modifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('content_id')->unsigned();
            $table->integer('editor_user_id')->unsigned();
            $table->text('new_content');
            

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('content_id')->references('id')->on('contents');
            $table->foreign('editor_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_modifications', function(Blueprint $table) {
            $table->dropForeign(['content_id']);
            $table->dropForeign(['editor_user_id']);
        });
        
        Schema::drop('content_modifications');
    }
}
