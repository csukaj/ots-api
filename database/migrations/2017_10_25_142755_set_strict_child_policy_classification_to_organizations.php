<?php

use App\Facades\Config;
use App\Organization;
use App\OrganizationClassification;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Stylerstaxonomy\Database\Seeders\TaxonomySeederTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class SetStrictChildPolicyClassificationToOrganizations extends Migration
{
    use TaxonomySeederTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $organizations = Organization::withTrashed()->get();
        $settingsTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.id');
        $strictBedPolicyTxData = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.strict_child_bed_policy');
        $rackPricesTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.strict_child_bed_policy.elements')['disabled'];

        $strictBedPolicyTx = $this->saveTaxonomy($strictBedPolicyTxData['id'], $strictBedPolicyTxData['name'],
            Taxonomy::find($settingsTxId));
        foreach ($strictBedPolicyTxData['elements'] as $elementName => $elementId) {
            $this->saveTaxonomy($elementId, $elementName, $strictBedPolicyTx);
        }

        foreach ($organizations as $organization) {
            $count = $organization->classifications()->where('classification_taxonomy_id', $strictBedPolicyTxData['id'])
                ->count();
            if (!$count) {
                $parentCl = $organization->classifications()->where('classification_taxonomy_id', $settingsTxId)
                    ->first();
                if(!$parentCl){
                    continue;
                }
                $orgCl = new OrganizationClassification([
                    'organization_id' => $organization->id,
                    'parent_classification_id' => $parentCl->id,
                    'classification_taxonomy_id' => $strictBedPolicyTxData['id'],
                    'value_taxonomy_id' => $rackPricesTxId
                ]);
                $orgCl->saveOrFail();
            }
        }

        Schema::table('device_classifications', function (Blueprint $table) {
            $table->boolean('is_listable')->default(0);
        });

        Schema::table('device_metas', function (Blueprint $table) {
            $table->integer('parent_classification_id')->unsigned()->nullable();
            $table->integer('priority')->unsigned()->nullable();
            $table->boolean('is_listable')->default(0);

            $table->foreign('parent_classification_id')->references('id')->on('device_classifications');
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
