<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('contactable_id')->unsigned();
            $table->string('contactable_type', 255);
            $table->integer('type_taxonomy_id')->unsigned();
            $table->string('value', 255);
            $table->string('extension', 255)->nullable();
            $table->integer('priority')->nullable();
            $table->boolean('is_public')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::table('contacts', function(Blueprint $table)
        {
            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function(Blueprint $table)
        {
            $table->dropForeign(['type_taxonomy_id']);
        });
        
        Schema::drop('contacts');
    }

}
