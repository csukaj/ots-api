<?php

namespace App\Entities;

use App\Facades\Config;
use App\OrganizationGroup;
use App\OrganizationGroupClassification;
use App\OrganizationGroupMeta;
use Modules\Stylersmedia\Entities\GalleryEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class OrganizationGroupEntity extends Entity
{

    const MODEL_TYPE = 'organization_group';
    const CONNECTION_COLUMN = 'organization_group_id';

    protected $organizationGroup;
    protected $nameDescription;
    protected $nameDescriptionTranslations;
    protected $ageRanges;
    protected $fromDate;
    protected $toDate;
    protected $productType;

    public function __construct(OrganizationGroup $organizationGroup, string $fromDate = null, string $toDate = null, string $productType = null)
    {
        parent::__construct();
        $this->organizationGroup = $organizationGroup;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        $this->nameDescription = $this->organizationGroup->name;
        $this->nameDescriptionTranslations = $this->nameDescription->translations;
        $this->ageRanges = $this->organizationGroup->ageRanges;
        $this->productType = $productType;
    }

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->organizationGroup->id,
            'name' => $this->getDescriptionWithTranslationsData($this->nameDescription),
            'type' => $this->organizationGroup->type->name,
            'is_active' => $this->organizationGroup->is_active,
            'properties' => []
        ];


        foreach ($additions as $addition) {
            switch ($addition) {
                case 'descriptions':
                    $return['descriptions'] = $this->getEntityDescriptionsData($this->organizationGroup->id,
                        Config::get('taxonomies.organization_group_description'));
                    break;

                case 'devices':
                    $deviceAdditions = $this->filterAdditions(
                        $additions, [
                            'usages',
                            'prices' => 'prices',
                            'device_margin' => 'margin',
                            'device_descriptions' => 'descriptions',
                            'device_amount' => 'amount'
                        ]
                    );
                    $return['devices'] = $this->getDevices($deviceAdditions);
                    break;

                case 'date_ranges':
                    $return['date_ranges'] = [
                        'open' => DateRangeEntity::getCollection($this->organizationGroup->dateRanges()->open()->orderBy('from_time')->get()),
                        'closed' => DateRangeEntity::getCollection($this->organizationGroup->dateRanges()->closed()->orderBy('from_time')->get()),
                        'price_modifier' => DateRangeEntity::getCollection($this->organizationGroup->dateRanges()->priceModifier()->orderBy('from_time')->get())
                    ];
                    break;

                case 'availability_mode':
                    $availabilityMode = $this->organizationGroup->getAvailabilityMode();
                    $return['availability_mode'] = $availabilityMode ? $availabilityMode->valueTaxonomy->name : null;
                    break;

                case 'pricing':
                    $return['pricing_logic'] = $this->organizationGroup->pricingLogic ? $this->organizationGroup->pricingLogic->name : null;
                    $return['margin_value'] = $this->organizationGroup->margin_value ? $this->organizationGroup->margin_value : null;
                    $return['margin_type'] = $this->organizationGroup->marginType ? $this->organizationGroup->marginType->name : null;
                    break;

                case 'parent':
                    $return['parent'] = $this->organizationGroup->parentOrganization ? (new OrganizationEntity($this->organizationGroup->parentOrganization))->getFrontendData() : null;
                    break;

                case 'prices':
                    $return['products'] = ProductEntity::getCollection($this->organizationGroup->products, ['prices']);
                    break;

                case 'supplier':
                    $return['supplier'] = $this->organizationGroup->supplier ? (new SupplierEntity($this->organizationGroup->supplier))->getFrontendData() : null;
                    break;

            }
        }

        return $return;
    }

    protected function getProperties(): array
    {
        return array_merge(
            OrganizationGroupMeta::getListableMetaEntitiesForModel(self::CONNECTION_COLUMN,
                $this->organizationGroup->id),
            $this->getClassifications()
        );
    }

    protected function getClassifications(): array
    {
        $models = OrganizationGroupClassification
            ::where(self::CONNECTION_COLUMN, $this->organizationGroup->id)
            ->listable()
            ->forParent(null)
            ->orderBy('priority')
            ->get();

        return OrganizationGroupClassificationEntity::getCollection($models, ['frontend']);
    }

    protected function getGalleries(): array
    {
        return GalleryEntity::getCollection($this->organizationGroup->galleries);
    }

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    protected function getDevices(array $additions = []): array
    {
        $devicesData = [];
        foreach ($this->organizationGroup->devices as $device) {
            if (!isset($devicesData[$device->type->name])) {
                $devicesData[$device->type->name] = [];
            }
            $devicesData[$device->type->name][] = (new DeviceEntity($device, $this->fromDate, $this->toDate, '', null,
                $this->productType))->getFrontendData($additions);
        }
        return $devicesData;
    }

    static public function getCategories(int $typeTxId): array
    {
        return TaxonomyEntity::getCollection(Taxonomy::findOrFail($typeTxId)->getChildren());
    }

    public static function getHardcodedSearchoptionTaxonomyIds(): array
    {
        return [];
    }
}
