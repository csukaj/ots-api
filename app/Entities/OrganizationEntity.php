<?php

namespace App\Entities;

use App\DateRange;
use App\Facades\Config;
use App\Organization;
use App\OrganizationClassification;
use App\OrganizationMeta;
use Modules\Stylersmedia\Entities\GalleryEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class OrganizationEntity extends Entity
{

    const MODEL_TYPE = 'organization';
    const CONNECTION_COLUMN = 'organization_id';

    protected $organization;
    protected $nameDescription;
    protected $nameDescriptionTranslations;
    protected $ageRanges;
    protected $fromDate;
    protected $toDate;
    protected $productType;

    public function __construct(Organization $organization, string $fromDate = null, string $toDate = null, string $productType = null)
    {
        parent::__construct();
        $this->organization = $organization;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;

        $this->nameDescription = $this->organization->name;
        $this->nameDescriptionTranslations = $this->nameDescription->translations;
        $this->ageRanges = $this->organization->ageRanges;
        $this->productType = $productType;
    }

    /**
     * @param array $additions
     * @param string|null $productType
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->organization->id,
            'name' => $this->getDescriptionWithTranslationsData($this->nameDescription),
            'short_name' => ($this->organization->shortName) ? $this->getDescriptionWithTranslationsData($this->organization->shortName) : null,
            'type' => $this->organization->type->name,
            'parent' => ($this->organization->parentOrganization) ? $this->getDescriptionWithTranslationsData($this->organization->parentOrganization->name) : null,
            'is_active' => $this->organization->is_active,
            'properties' => [],
            'reviews' => ReviewEntity::getCollection($this->organization->reviews)
        ];


        foreach ($additions as $addition) {
            switch ($addition) {
                case 'descriptions':
                    $return['descriptions'] = $this->getEntityDescriptionsData($this->organization->id,
                        Config::get('taxonomies.organization_description'));
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
                        'open' => DateRangeEntity::getCollection($this->organization->dateRanges()->open()->orderBy('from_time')->get()),
                        'closed' => DateRangeEntity::getCollection($this->organization->dateRanges()->closed()->orderBy('from_time')->get()),
                        'price_modifier' => DateRangeEntity::getCollection($this->organization->dateRanges()->priceModifier()->orderBy('from_time')->get())
                    ];
                    break;

                case 'location':
                    $return['location'] = $this->organization->location_id ? (new LocationEntity($this->organization->location))->getFrontendData(['admin']) : null;
                    break;

                case 'availability_mode':
                    $availabilityMode = $this->organization->getAvailabilityMode();
                    $return['availability_mode'] = $availabilityMode ? $availabilityMode->valueTaxonomy->name : null;
                    break;

                case 'pricing':
                    $return['pricing_logic'] = $this->organization->pricingLogic ? $this->organization->pricingLogic->name : null;
                    $return['margin_type'] = $this->organization->marginType ? $this->organization->marginType->name : null;
                    break;

                case 'supplier':
                    $suppliersAddition = [];
                    if (in_array('contacts', $additions)) {
                        $suppliersAddition[] = 'contacts';
                    }
                    if (in_array('people', $additions)) {
                        $suppliersAddition[] = 'people';
                    }

                    $return['supplier'] = $this->organization->supplier ? (new SupplierEntity($this->organization->supplier))->getFrontendData($suppliersAddition) : null;
                    break;
            }
        }

        return $return;
    }

    protected function getProperties(): array
    {
        return array_merge(
            OrganizationMeta::getListableMetaEntitiesForModel(self::CONNECTION_COLUMN, $this->organization->id),
            $this->getClassifications()
        );
    }

    protected function getClassifications(): array
    {
        $models = OrganizationClassification
            ::where(self::CONNECTION_COLUMN, $this->organization->id)
            ->listable()
            ->forParent(null)
            ->with([
                'classificationTaxonomy',
                'valueTaxonomy',
                'chargeTaxonomy',
                'additionalDescription',
                'listableChildClassifications',
                'childMetas'
            ])
            ->orderBy('priority')
            ->get();

        return OrganizationClassificationEntity::getCollection($models, ['frontend']);
    }

    protected function getGalleries(): array
    {
        return GalleryEntity::getCollection($this->organization->galleries()->with(['name', 'role', 'items'])->get(),
            ['highlightedFirst']);
    }

    protected function getDevices(array $additions = []): array
    {
        $devicesData = [];
        foreach ($this->organization->devices()->with(['name', 'type'])->get() as $device) {
            if (!isset($devicesData[$device->type->name])) {
                $devicesData[$device->type->name] = [];
            }
            $devicesData[$device->type->name][] = (new DeviceEntity($device, $this->fromDate,
                $this->toDate,'',null,$this->productType))->getFrontendData($additions);
        }
        return $devicesData;
    }

    static public function getCategories(int $typeTxId): array
    {
        return TaxonomyEntity::getCollection(Taxonomy::findOrFail($typeTxId)->getChildren());
    }

    public function getMealPlans(): array
    {
        $mealPlans = [];
        if (!$this->fromDate || !$this->toDate) {
            $modelMealPlans = $this->organization->modelMealPlans()->with('mealPlan')->get();
        } else {
            $dateRangeIds = DateRange
                ::getDateRangesInInterval($this->organization->id, $this->fromDate, $this->toDate,
                    Config::get('taxonomies.date_range_types.open'))
                ->pluck('id');
            $modelMealPlans = $this->organization
                ->modelMealPlans()
                ->with('mealPlan')
                ->whereIn('date_range_id', $dateRangeIds)
                ->get();
        }
        foreach ($modelMealPlans as $modelMealPlan) {
            $mealPlans[] = (new MealPlanEntity($modelMealPlan->mealPlan))->getFrontendData();
        }
        return array_merge(array_unique($mealPlans, SORT_REGULAR));
    }

    public function getSearchOptions(): array
    {
        $options = [];
        if (!empty($this->organization->parentOrganization)) {
            $options['Hotel Chain'] = $this->organization->parentOrganization->name->description;
        }

        // hard-coded
        $hardcodedTxIds = self::getHardcodedSearchoptionTaxonomyIds();
        $hardcodedOptions = $this->organization
            ->classifications()
            ->with(['classificationTaxonomy', 'valueTaxonomy'])
            ->whereIn('classification_taxonomy_id', $hardcodedTxIds)
            ->get();
        foreach ($hardcodedOptions as $hco) {
            $options[$hco->classificationTaxonomy->name] = $hco->valueTaxonomy ? $hco->valueTaxonomy->name : null;
        }

        // dynamic
        $orgCls = $this->organization
            ->classifications()
            ->searchable()
            ->with(['classificationTaxonomy', 'valueTaxonomy'])
            ->whereNotIn('classification_taxonomy_id', $hardcodedTxIds)
            ->get();
        foreach ($orgCls as $orgCl) {
            $options[$orgCl->classificationTaxonomy->name] = ($orgCl->valueTaxonomy) ? $orgCl->valueTaxonomy->name : true;
        }
        return $options;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getHardcodedSearchoptionTaxonomyIds(): array
    {
        return [
            Config::getOrFail('taxonomies.organization_properties.categories.general.items.accommodation_category.id'),
            Config::getOrFail('taxonomies.organization_properties.categories.settings.items.stars.id')
        ];
    }
}
