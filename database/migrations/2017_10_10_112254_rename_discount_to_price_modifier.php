<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameDiscountToPriceModifier extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::rename('discounts', 'price_modifiers');

        Schema::rename('discount_combinations', 'price_modifier_combinations');
        Schema::table('price_modifier_combinations', function (Blueprint $table) {
            $table->renameColumn('first_discount_id', 'first_price_modifier_id');
            $table->renameColumn('second_discount_id', 'second_price_modifier_id');
        });

        Schema::rename('discount_periods', 'price_modifier_periods');
        Schema::table('price_modifier_periods', function (Blueprint $table) {
            $table->renameColumn('discount_id', 'price_modifier_id');
        });

        Schema::rename('discount_classifications', 'price_modifier_classifications');
        Schema::table('price_modifier_classifications', function (Blueprint $table) {
            $table->renameColumn('discount_id', 'price_modifier_id');
        });

        Schema::rename('discount_metas', 'price_modifier_metas');
        Schema::table('price_modifier_metas', function (Blueprint $table) {
            $table->renameColumn('discount_id', 'price_modifier_id');
        });

        Schema::table('offer_classifications', function (Blueprint $table) {
            $table->renameColumn('discount_id', 'price_modifier_id');
        });

        Schema::table('offer_metas', function (Blueprint $table) {
            $table->renameColumn('discount_id', 'price_modifier_id');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
