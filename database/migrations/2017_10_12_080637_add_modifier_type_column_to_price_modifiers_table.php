<?php

use App\Facades\Config;
use App\PriceModifier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class AddModifierTypeColumnToPriceModifiersTable extends Migration
{
    use TaxonomySeederTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tx = $this->saveTaxonomyWithChildren('taxonomies.price_modifier_type');

        Schema::table('price_modifiers', function (Blueprint $table) {
            $table->integer('modifier_type_taxonomy_id')->nullable()->unsigned();
            $table->renameColumn('type_taxonomy_id', 'condition_taxonomy_id');
        });
        PriceModifier::withTrashed()->update(['modifier_type_taxonomy_id' => Config::getOrFail('taxonomies.price_modifier_types.discount.id')]);
        Schema::table('price_modifiers', function (Blueprint $table) {
            $table->integer('modifier_type_taxonomy_id')->nullable(false)->change();
            $table->foreign('modifier_type_taxonomy_id')->references('id')->on('taxonomies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_modifiers', function (Blueprint $table) {
            $table->dropForeign(['modifier_type_taxonomy_id']);
            $table->dropColumn('modifier_type_taxonomy_id');
        });
    }
}
