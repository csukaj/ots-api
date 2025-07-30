<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContentMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('content_id')->unsigned();
            $table->integer('mediable_id')->unsigned();
            $table->string('mediable_type', 255);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['content_id', 'mediable_id', 'mediable_type']);
            $table->foreign('content_id')->references('id')->on('contents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_media', function(Blueprint $table) {
            $table->dropForeign(['content_id']);
        });
        Schema::drop('content_files');
    }
}
