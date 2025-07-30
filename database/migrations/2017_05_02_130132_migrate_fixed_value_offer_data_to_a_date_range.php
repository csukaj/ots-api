<?php

use App\Facades\Config;
use App\OfferMeta;
use Illuminate\Database\Migrations\Migration;

class MigrateFixedValueOfferDataToADateRange extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $offerMetas = OfferMeta::withTrashed()->where('taxonomy_id', '=', Config::getOrFail('taxonomies.price_modifier_offers.fixed_price.metas.modifier_value.id'))->get();
        foreach ($offerMetas as $offerMeta) {
            if(is_numeric($offerMeta->value)){
                $offerMeta->value = '{"adult":' . intval($offerMeta->value) . '}';
                $offerMeta->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        $offerMetas = OfferMeta::withTrashed()->where('taxonomy_id', '=', Config::getOrFail('taxonomies.price_modifier_offers.fixed_price.metas.modifier_value.id'))->get();
        foreach ($offerMetas as $offerMeta) {
            $decoded = \json_decode($offerMeta->value, true);
            $offerMeta->value = ($decoded && !empty($decoded['adult'])) ? $decoded['adult'] : '';
            $offerMeta->save();
        }
    }

}
