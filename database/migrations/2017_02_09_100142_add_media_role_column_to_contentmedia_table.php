<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMediaRoleColumnToContentmediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('content_media', function (Blueprint $table) {
            $table->integer('media_role_taxonomy_id')->unsigned();
            
            $table->foreign('media_role_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('content_media', function (Blueprint $table) {
            $table->dropColumn('media_role_taxonomy_id');
        });
    }
}
