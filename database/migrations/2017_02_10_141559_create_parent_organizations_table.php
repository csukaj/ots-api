<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParentOrganizationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('parent_organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique('name');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('parent_organizations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('organizations');
        });
        Schema::drop('parent_organizations');
    }

}
