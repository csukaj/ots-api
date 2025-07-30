<?php

use App\Facades\Config;
use App\Organization;
use App\OrganizationClassification;
use Illuminate\Database\Seeder;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;

class MergedFreeNightsDefaultValueSeeder extends Seeder
{

    use TaxonomySeederTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $organizations = Organization::all();
        $settingsTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.id');
        $mergedFreeNightsTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.merged_free_nights.id');
        $enabledTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.merged_free_nights.elements')['enabled'];

        foreach ($organizations as $organization) {
            $count = $organization->classifications()
                ->where('classification_taxonomy_id', '=', $mergedFreeNightsTxId)
                ->count();
            if (!$count) {
                $parentCl = $organization->classifications()->where('classification_taxonomy_id', '=', $settingsTxId)->first();
                if(!$parentCl)
                    continue;
                $orgCl = new OrganizationClassification([
                    'organization_id' => $organization->id,
                    'parent_classification_id' => $parentCl->id,
                    'classification_taxonomy_id' => $mergedFreeNightsTxId,
                    'value_taxonomy_id' => $enabledTxId,
                    'priority' => 1
                ]);
                $orgCl->saveOrFail();
            }
        }
    }
}
