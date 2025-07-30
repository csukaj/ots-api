<?php

use App\Facades\Config;
use App\Organization;
use Illuminate\Database\Migrations\Migration;

class AddDiscountCalculationsBaseToOrganizations extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $organizations = Organization::all();
        $settingsTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.id');
        $discountCalculationsBaseTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.discount_calculations_base.id');
        $rackPricesTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.discount_calculations_base.elements')['rack prices'];

        foreach ($organizations as $organization) {
            $count = $organization->classifications()
                    ->where('classification_taxonomy_id', '=', $discountCalculationsBaseTxId)
                    ->count();
            if (!$count) {
                $parentCl = $organization->classifications()->where('classification_taxonomy_id', '=', $settingsTxId)->firstOrFail();
                $orgCl = new \App\OrganizationClassification([
                    'organization_id' => $organization->id,
                    'parent_classification_id' => $parentCl->id,
                    'classification_taxonomy_id' => $discountCalculationsBaseTxId,
                    'value_taxonomy_id' => $rackPricesTxId
                ]);
                $orgCl->saveOrFail();
            }
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
