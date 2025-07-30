<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('review_subject');
            $table->unsignedInteger('author_user_id');
            $table->unsignedInteger('review_description_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('author_user_id')->references('id')->on('users');
            $table->foreign('review_description_id')->references('id')->on('descriptions');
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
            $table->dropForeign(['author_user_id']);
            $table->dropForeign(['review_description_id']);
        });
        Schema::dropIfExists('reviews');
    }
}
