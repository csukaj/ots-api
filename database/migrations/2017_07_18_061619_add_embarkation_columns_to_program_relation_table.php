<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmbarkationColumnsToProgramRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('program_relations', function (Blueprint $table) {
            $table->integer('embarkation_type_taxonomy_id')->unsigned()->nullable();
            $table->integer('embarkation_direction_taxonomy_id')->unsigned()->nullable();
            
            $table->foreign('embarkation_type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('embarkation_direction_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('program_relations', function (Blueprint $table) {
            $table->dropColumn('embarkation_type_taxonomy_id');
            $table->dropColumn('embarkation_direction_taxonomy_id');
        });
    }
}
