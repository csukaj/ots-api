<?php

namespace App\Entities;

use App\Facades\Config;
use App\OrganizationGroupClassification;
use App\OrganizationGroupMeta;
use App\ShipCompany;
use App\ShipGroup;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ShipGroupEntity extends OrganizationGroupEntity
{

    public function __construct(ShipGroup $organizationGroup, string $fromDate = null, string $toDate = null, string $productType = null)
    {
        parent::__construct($organizationGroup, $fromDate, $toDate, $productType);
    }

    /**
     *
     * @param array $additions
     * @param string $productType
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $return = parent::getFrontendData($additions);
        $return['amount'] = $this->organizationGroup->getShipCount();
        $return['deviceable_type'] = ShipGroup::class;

        $devicesAdditions = array_merge(['descriptions', 'properties', 'images', 'amount'], $additions);
        foreach ($additions as $addition) {
            switch ($addition) {
                case 'info':
                    $return['locations'] = LocationEntity::getCollection($this->organizationGroup->getHomePortLocations(), ['frontend']);
                    $return['devices'] = $this->getDevices($devicesAdditions);
                    $return['descriptions'] = $this->getEntityDescriptionsData($this->organizationGroup->id, Config::get('taxonomies.organization_description'));
                    $return['properties'] = $this->getProperties();
                    $return['galleries'] = $this->getGalleries();
                    $return['settings'] = $this->getSettings();
                    $return['age_ranges'] = AgeRangeEntity::getCollection($this->organizationGroup->ageRanges, ['frontend']);
                    $return['search_options'] = $this->getSearchOptions();
                    $return['optional_fees'] = $this->getOptionalFees();
                    break;

                case 'admin_properties':
                    $return['properties'] = $this->getAdminProperties();
                    break;

                case 'parent':
                    $return['parent'] = $this->organizationGroup->parentOrganization ? (new OrganizationEntity($this->organizationGroup->parentOrganization))->getFrontendData() : null;
                    break;

                case 'galleries':
                    $return['galleries'] = $this->getGalleries();
                    break;

                case 'devices':
                    $return['devices'] = $this->getDevices($devicesAdditions);
                    break;

                case 'ship_company':
                    $return['ship_company'] = $this->organizationGroup->parent_id ?
                        (new ShipCompanyEntity(ShipCompany::findOrFail($this->organizationGroup->parent_id)))->getFrontendData(['descriptions', 'galleries']) :
                        null;
                    break;

            }
        }

        return $return;
    }

    /**
     *
     * @return array
     */
    private function getSettings(): array
    {
        $models = [];
        $settings = [
            'taxonomies.organization_properties.categories.settings.items.price_level.id',
            'taxonomies.organization_properties.categories.settings.items.stars.id'
        ];
        foreach ($settings as $settingIdPath) {
            $model = OrganizationGroupClassification::getClassification(
                self::CONNECTION_COLUMN, $this->organizationGroup->id, Config::get($settingIdPath)
            );
            if (!empty($model)) {
                $models[] = $model;
            }
        }
        return OrganizationGroupClassificationEntity::getCollection($models, ['frontend']);
    }

    /**
     *
     * @return array
     */
    private function getAdminProperties(): array
    {
        $models = [];
        $settings = [
            'taxonomies.organization_group_properties.categories.general.items.ship_group_category.id'
        ];
        foreach ($settings as $settingIdPath) {
            $model = OrganizationGroupClassification::getClassification(
                self::CONNECTION_COLUMN, $this->organizationGroup->id, Config::get($settingIdPath)
            );
            if (!empty($model)) {
                $models[] = $model;
            }
        }
        return OrganizationGroupClassificationEntity::getCollection($models, ['frontend']);
    }

    public function getSearchOptions(): array
    {
        $options = [];

        // hard-coded
        $hardcodedTxIds = self::getHardcodedSearchoptionTaxonomyIds();
        $hardcodedOptions = $this->organizationGroup->classifications()->whereIn('classification_taxonomy_id',
            $hardcodedTxIds)->get();
        foreach ($hardcodedOptions as $hco) {
            $options[$hco->classificationTaxonomy->name] = $hco->valueTaxonomy->name;
        }

        // dynamic
        $orgCls = $this->organizationGroup->classifications()->searchable()->whereNotIn('classification_taxonomy_id',
            $hardcodedTxIds)->get();
        foreach ($orgCls as $orgCl) {
            $options[$orgCl->classificationTaxonomy->name] = ($orgCl->valueTaxonomy) ? $orgCl->valueTaxonomy->name : true;
        }

        $orgMts = $this->organizationGroup->metas()->searchable()->whereNotIn('taxonomy_id', $hardcodedTxIds)->get();
        foreach ($orgMts as $orgMt) {
            $options[$orgMt->metaTaxonomy->name] = $orgMt->value;
        }
        return $options;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getOptionalFees(): array
    {
        $options = [];
        $parentClassification = $this->organizationGroup
            ->classifications()
            ->where('classification_taxonomy_id',
                Config::getOrFail('taxonomies.organization_group_properties.categories.options.id'))
            ->first();
        if($parentClassification) {
            $models = OrganizationGroupMeta
                ::where(self::CONNECTION_COLUMN, $this->organizationGroup->id)
                ->forParent($parentClassification->id)
                ->orderBy('priority')
                ->get();
            foreach ($models as $orgGrMt) {
                $options[]  = [
                    'name'=> (new TaxonomyEntity($orgGrMt->metaTaxonomy))->translations(),
                    'rack_price' => floatval($orgGrMt->value)
                ];
            }
        }
        return $options;
    }


}
