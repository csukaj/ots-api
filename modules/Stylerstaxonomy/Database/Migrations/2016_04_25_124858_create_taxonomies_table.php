<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;

/**
 * @codeCoverageIgnore
 */
class CreateTaxonomiesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('taxonomies', function(Blueprint $table) {
            // These columns are needed for Baum's Nested Set implementation to work.
            // Column names may be changed, but they *must* all exist and be modified
            // in the model.
            // Take a look at the model scaffold comments for details.
            // We add indexes on parent_id, lft, rgt columns by default.
            $table->increments('id');
            $table->integer('parent_id')->nullable()->index();
            $table->integer('lft')->nullable()->index();
            $table->integer('rgt')->nullable()->index();
            $table->integer('depth')->nullable();

            // Add needed columns here (f.ex: name, slug, path, etc.)
            $table->string('name', 255);
            $table->integer('priority')->unsigned()->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_required')->default(0);
            $table->boolean('is_merchantable')->default(0);
            $table->enum('type', [
                Config::get('stylerstaxonomy.type_unknown'),
                Config::get('stylerstaxonomy.type_int'),
                Config::get('stylerstaxonomy.type_double'),
                Config::get('stylerstaxonomy.type_string'),
                Config::get('stylerstaxonomy.type_date'),
                Config::get('stylerstaxonomy.type_phone'),
                Config::get('stylerstaxonomy.type_email'),
                Config::get('stylerstaxonomy.type_classification'),
                Config::get('stylerstaxonomy.type_meta')
            ])->default(Config::get('stylerstaxonomy.type_unknown'));
            $table->string('relation', 255)->nullable();
            $table->string('icon', 255)->nullable();

            $table->timestamps();
            $table->softdeletes();
        });

        Illuminate\Support\Facades\DB::statement('ALTER SEQUENCE taxonomies_id_seq RESTART WITH 10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('taxonomies');
    }

}
