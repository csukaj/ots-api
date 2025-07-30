<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * @codeCoverageIgnore
 */
class AddIsReadonlyFieldToTaxonomiesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('taxonomies', function (Blueprint $table) {
            $table->boolean('is_readonly')->default(false)->after('is_required');
        });
        DB::statement('UPDATE taxonomies SET is_readonly = true WHERE id < 10000');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('taxonomies', function (Blueprint $table) {
            $table->dropColumn('is_readonly');
        });
    }

}
