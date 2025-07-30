<?php

use App\PriceModifier;
use App\Organization;
use Illuminate\Database\Migrations\Migration;

class ReorderDiscounts extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $organizations = Organization::all();
        foreach ($organizations as $org) {

            $sampleDiscount = PriceModifier::forModel(Organization::class, $org->id)->first();
            if ($sampleDiscount) {
                $siblings = $sampleDiscount->findSiblingsInOrder(true);

                foreach (PriceModifier::sortbyPriority($siblings) as $idx => $siblingDiscount) {
                    $siblingDiscount->priority = $idx + 1;
                    $siblingDiscount->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //do nothing - we don't know previous order
    }

}
