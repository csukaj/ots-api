<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ConvertWeddingDiscountMonthsToDays extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $inMonthsTaxonomies = [105, 106]; //hardcoded because renamed
        DB::table('discount_metas')
                ->whereIn('taxonomy_id', $inMonthsTaxonomies)
                ->update(['value' => DB::raw("CAST(coalesce(value, '0') AS integer) * 30")]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $inMonthsTaxonomies = [105, 106]; //hardcoded because renamed
        DB::table('discount_metas')
                ->whereIn('taxonomy_id', $inMonthsTaxonomies)
                ->update(['value' => DB::raw("CAST(coalesce(value, '0') AS integer) / 30")]);
    }

}
