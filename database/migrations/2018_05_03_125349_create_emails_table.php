<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('type_taxonomy_id')->unsigned();
            $table->integer('subject_description_id')->unsigned();
            $table->integer('content_description_id')->unsigned();
            $table->timestamps();

            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('subject_description_id')->references('id')->on('descriptions');
            $table->foreign('content_description_id')->references('id')->on('descriptions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['subject_description_id']);
            $table->dropForeign(['content_description_id']);
        });

        Schema::dropIfExists('emails');
    }
}
