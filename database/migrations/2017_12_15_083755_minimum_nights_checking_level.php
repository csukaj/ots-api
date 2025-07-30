<?php

use App\Facades\Config;
use App\PriceModifier;
use App\PriceModifierClassification;
use App\PriceModifierMeta;
use Illuminate\Database\Migrations\Migration;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class MinimumNightsCheckingLevel extends Migration
{
    use TaxonomySeederTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if(!PriceModifier::all()->count()){
            return;
        }

        //pre-create desired taxonomies
        $tx = $this->saveTaxonomyWithChildren('taxonomies.minimum_nights_checking_level');

        foreach (Config::getOrFail('taxonomies.price_modifier_application_levels') as $applicationLevel) {
            foreach ($applicationLevel['price_modifier_condition_types'] as $type) {
                $metas = $type['metas'];
                $parentTx = Taxonomy::findOrFail($type['meta']);
                if (isset($metas['minimum_nights_checking_level'])) {
                    $txData = $metas['minimum_nights_checking_level'];
                    $this->saveTaxonomy($txData['id'], 'minimum_nights_checking_level', $parentTx, $txData);
                }
            }
        }

        //update price modifiers
        $priceModifiers = PriceModifier::all();
        foreach ($priceModifiers as $priceModifier) {
            $hasBDSC = false;
            $hasMinOrMaxNights = false;
            $classifications = (new PriceModifierClassification())->getClassificationEntities('price_modifier_id',
                $priceModifier->id);
            $metas = (new PriceModifierMeta())->getMetaEntities('price_modifier_id', $priceModifier->id);
            foreach ($classifications as $cl) {
                if ($cl['name'] == 'booking_dates_should_be_contained') {
                    $hasBDSC = true;
                }
            }

            foreach ($metas as $cl) {
                if (in_array($cl['name'], ['minimum_nights', 'maximum_nights'])) {
                    $hasMinOrMaxNights = true;
                }
            }

            if ($hasBDSC && !$hasMinOrMaxNights) {
                $valueTxId = Config::getOrFail('taxonomies.minimum_nights_checking_levels.booking_dates_should_be_contained.id');
            } else {
                $valueTxId = Config::getOrFail('taxonomies.minimum_nights_checking_levels.minimum_nights_in_discount_period.id');
            }


            if ($hasBDSC || $hasMinOrMaxNights) {
                $conditionTaxonomy = Taxonomy::getTaxonomyGrandChild($priceModifier->condition->name,
                    Config::getOrFail('taxonomies.price_modifier_application_level'));
                $parentTxName = $conditionTaxonomy->parent->name;
                $parentId = Config::getOrFail('taxonomies.price_modifier_application_levels.' . $parentTxName . '.price_modifier_condition_types.' . $conditionTaxonomy->name . '.meta');
                $data = ['name' => 'minimum_nights_checking_level', 'value' => $valueTxId];
                (new PriceModifierMeta())
                    ->insertOrUpdateMeta('price_modifier_id', $priceModifier->id, $parentId, $data);
            }
        }

        //remove not needed metas and taxonomies
        PriceModifierClassification
            ::whereIn('value_taxonomy_id', [75, 311])
            ->delete();
        $BDSCTxs = Taxonomy::find([75, 311]);
        foreach ($BDSCTxs as $tx) {
            $tx->delete();
        }
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
