<?php

use App\OfferMeta;
use Illuminate\Database\Migrations\Migration;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class DeleteDeductionBaseUsageOfferMetas extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $txIds = Taxonomy::where('name', '=', 'deduction_base_usage')->get()->pluck('id');
        if (count($txIds)) {
            OfferMeta::withTrashed()->whereIn('taxonomy_id', $txIds)->forceDelete();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
