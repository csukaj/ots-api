<?php

namespace App\Console\Commands\TestModelSeeder;

use App\AgeRange;
use App\Console\Commands\TestModelPropertySeeder;
use App\DateRange;
use App\Device;
use App\DeviceDescription;
use App\DeviceMeta;
use App\DeviceUsage;
use App\DeviceUsageElement;
use App\Facades\Config;
use App\Manipulators\AvailabilitySetter;
use App\Manipulators\DeviceMinimumNightsSetter;
use App\Manipulators\LocationSetter;
use App\Manipulators\OrganizationGroupSetter;
use App\Manipulators\OrganizationSetter;
use App\Organization;
use App\OrganizationDescription;
use App\OrganizationGroupDescription;
use App\Review;
use App\Ship;
use App\ShipGroup;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyTranslation;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Class to seed a accommodation test data
 */
class TestOrganizationSeeder
{

    protected $parentClassName;

    public function __construct(string $parentClassName)
    {
        $this->parentClassName = $parentClassName;
    }

    /**
     * Seed a accommodation with all associated data, including price modifiers
     *
     * @param array $data
     * @return array
     * @throws \Exception
     * @throws \Throwable
     */
    public function seed(array $data): array
    {
        list($organization, $dateRangeRelativeIds) = $this->setOrganization($data);
        TestModelSeeder::setAgeRanges($this->parentClassName, $organization->id, $data['age_ranges']);
        $this->setDevices($organization, $data, $dateRangeRelativeIds);
        (new TestPriceModifierSeeder())->seed($this->parentClassName, $organization->id, $data, $dateRangeRelativeIds);
        return [$organization, $dateRangeRelativeIds];
    }

    /**
     * Creates (or get) Taxonomy data and adds translations to it.
     * @param array $taxonomyData
     * @param int $parentTxId
     * @return Taxonomy
     */
    private function setTaxonomyObj(array $taxonomyData, int $parentTxId)
    {
        $languages = Language::getLanguageCodes();

        $tx = Taxonomy::getOrCreateTaxonomy($taxonomyData['en'], $parentTxId);

        foreach ($taxonomyData as $languageCode => $name) {
            if ($languageCode == 'en') {
                continue;
            }

            $txTranslation = new TaxonomyTranslation([
                'language_id' => $languages[$languageCode],
                'taxonomy_id' => $tx->id,
                'name' => $name
            ]);
            $txTranslation->save();
        }

        return $tx;
    }

    /**
     * Creates a DeviceUsageElement with provided data
     *
     * @param int $deviceUsageId
     * @param int $ageRangeId
     * @param int $amount
     * @return int
     */
    private function setDeviceUsageElement(int $deviceUsageId, int $ageRangeId, int $amount): int
    {
        $deviceUsageElement = new DeviceUsageElement([
            'device_usage_id' => $deviceUsageId,
            'age_range_id' => $ageRangeId,
            'amount' => $amount
        ]);
        $deviceUsageElement->save();

        return $deviceUsageElement->id;
    }

    /**
     * Creates a Availability object with provided data
     *
     * @param int $deviceId
     * @param array $availabilityData
     * @return int
     * @throws \Throwable
     */
    private function setDeviceAvailability(int $deviceId, array $availabilityData)
    {
        $setter = new AvailabilitySetter([
            'availableType' => Device::class,
            'availableId' => $deviceId,
            'fromDate' => $availabilityData['from_time'],
            'toDate' => $availabilityData['to_time'],
            'amount' => $availabilityData['amount']
        ]);
        return $setter->set()->id;
    }

    /**
     * Creates a DeviceMinimumNights object with provided data
     *
     * @param int $deviceId
     * @param array $minNightsData
     * @param array $dateRangeRelativeIds
     * @return int
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    private function setDeviceMinimumNights(int $deviceId, array $minNightsData, array $dateRangeRelativeIds)
    {
        $rangeId = $dateRangeRelativeIds[$minNightsData['date_range_relative_id']];
        $setter = new DeviceMinimumNightsSetter([
            'device_id' => $deviceId,
            'date_range_id' => $rangeId,
            'minimum_nights' => $minNightsData['minimum_nights']
        ]);
        return $setter->set()->id;
    }

    /**
     * Creates a DeviceUsage object with provided data. It also creates elements
     *
     * @param int $organizationId
     * @param int $deviceId
     * @param array $usageData
     * @return int
     */
    private function setDeviceUsage(int $organizationId, int $deviceId, array $usageData)
    {
        $deviceUsage = new DeviceUsage([
            'device_id' => $deviceId
        ]);
        $deviceUsage->save();

        foreach ($usageData['elements'] as $ageRangeTxName => $amount) {
            $ageRange = AgeRange::findByNameOrFail($ageRangeTxName, $this->parentClassName, $organizationId);
            $this->setDeviceUsageElement($deviceUsage->id, $ageRange->id, $amount);
        }

        return $deviceUsage->id;
    }

    /**
     * Creates a Product object with all associated data
     *
     * @param Model $parentModel
     * @param int $deviceTypeTxId
     * @param array $deviceData
     * @param array $dateRangeRelativeIds
     * @return int
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    private function setDevice(
        Model $parentModel,
        int $deviceTypeTxId,
        array $deviceData,
        array $dateRangeRelativeIds
    ) {
        $nameTx = $this->setTaxonomyObj($deviceData['name'], Config::getOrFail('taxonomies.names.device_name'));

        $device = new Device([
            'type_taxonomy_id' => $deviceTypeTxId,
            'name_taxonomy_id' => $nameTx->id,
            'amount' => $deviceData['amount']
        ]);
        $parentModel->devices()->save($device);

        if (isset($deviceData['margin_value'])) {
            $device->margin_value = $deviceData['margin_value'];
        }

        $device->save();

        $this->setDeviceClassifications($device, $deviceData);

        if (!empty($deviceData['metas'])) {
            $metas = [];
            foreach ($deviceData['metas'] as $key => $value) {
                $metas[] = ['name' => $key, 'value' => $value, 'is_listable' => true, 'priority' => count($metas)];
            }
            (new DeviceMeta())->setMetas('device_id', $device->id, Config::getOrFail('taxonomies.device_meta'), $metas);
        }

        TestModelSeeder::setModelDescriptions(DeviceDescription::class, $device->id, 'device_id', 'device_descriptions',
            $deviceData);

        TestModelSeeder::setGallery(get_class($device), $device,
            !empty($deviceData['media']) ? $deviceData['media'] : []);

        foreach ($deviceData['usages'] as $usageData) {
            $this->setDeviceUsage($parentModel->id, $device->id, $usageData);
        }

        foreach ($deviceData['availabilities'] as $availabilityData) {
            $this->setDeviceAvailability($device->id, $availabilityData);
        }

        if (!empty($deviceData['minimum_nights'])) {
            foreach ($deviceData['minimum_nights'] as $minNightsData) {
                $this->setDeviceMinimumNights($device->id, $minNightsData, $dateRangeRelativeIds);
            }
        }

        $productSeederParams = [
            'modelType' => $this->parentClassName,
            'modelId' => $parentModel->id,
            'productableType' => Device::class,
            'productableId' => $device->id,
        ];
        (new TestProductSeeder())->seed($productSeederParams, $dateRangeRelativeIds, $deviceData);

        return $device->id;
    }

    /**
     * set Device Classifications
     *
     * @param Device $device
     * @param array $deviceData
     * @throws \Exception
     * @throws \Throwable
     */
    private function setDeviceClassifications(Device $device, array $deviceData)
    {
        foreach ($deviceData['classifications'] as $parentKey => $parentClData) {
            $parentTxId = Config::getOrFail("taxonomies.device_properties.categories.{$parentKey}.id");
            $parentCl = $device->setClassification($parentTxId, null);
            if (!empty($parentClData['additional_description'])) {
                $additionalDn = new Description(['description' => $parentClData['additional_description']]);
                $additionalDn->saveOrFail();
                $parentCl->additional_description_id = $additionalDn->id;
            }
            $parentCl->is_listable = !empty($parentClData['is_listable']);
            $parentCl->saveOrFail();

            if (!empty($parentClData['items'])) {
                foreach ($parentClData['items'] as $childId => $childItem) {
                    if (is_numeric($childId)) {
                        $txId = Config::getOrFail("taxonomies.device_properties.categories.{$parentKey}.items.{$childItem}.id");
                        $valueTxId = null;
                    } else {
                        $txId = Config::getOrFail("taxonomies.device_properties.categories.{$parentKey}.items.{$childId}.id");
                        $valueTxId = Config::getOrFail("taxonomies.device_properties.categories.{$parentKey}.items.{$childId}.elements." . $childItem);
                    }
                    $childCl = $device->setClassification($txId, $valueTxId);
                    $childCl->parent_classification_id = $parentCl->id;
                    $childCl->saveOrFail();
                }
            }
            if (!empty($parentClData['metas'])) {
                $metas = [];
                foreach ($parentClData['metas'] as $key => $value) {
                    $metas[] = [
                        'name' => $key,
                        'value' => $value,
                        'parent_classification_id' => $parentCl->id,
                        'is_listable' => false,
                        'priority' => count($metas)
                    ];
                }
                (new DeviceMeta())->setMetas('device_id', $device->id, $parentTxId, $metas);
            }
        }
    }

    /**
     * Set devices for an organization
     *
     * @param Model $model
     * @param array $orgData
     * @param array $dateRangeRelativeIds
     * @throws \Exception
     * @throws \Throwable
     */
    private function setDevices(Model $model, array $orgData, array $dateRangeRelativeIds)
    {
        $devices = Config::getOrFail('taxonomies.devices');

        foreach ($devices as $key => $deviceTypeTxId) {
            if (!isset($orgData['devices'][$key])) {
                continue;
            }

            foreach ($orgData['devices'][$key] as $deviceData) {
                $this->setDevice($model, $deviceTypeTxId, $deviceData, $dateRangeRelativeIds);
            }
        }
    }

    /**
     * set Organization Descriptions
     *
     * @param int $modelId
     * @param array $data
     * @throws \Exception
     */
    private function setOrganizationDescriptions(int $modelId, array $data)
    {
        switch ($this->parentClassName) {
            case Organization::class:
                $descriptionClassName = OrganizationDescription::class;
                $columnName = 'organization_id';
                $taxonomyName = 'organization_descriptions';
                break;

            case ShipGroup::class:
                $descriptionClassName = OrganizationGroupDescription::class;
                $columnName = 'organization_group_id';
                $taxonomyName = 'organization_group_descriptions';
                break;

            default:
                throw new \Exception("Unsupported parent class: `{$this->parentClassName}`!");
        }

        TestModelSeeder::setModelDescriptions($descriptionClassName, $modelId, $columnName, $taxonomyName, $data);
    }

    /**
     * set Organization Descriptions
     *
     * @param int $modelId
     * @param array $data
     * @throws \Exception
     * @throws \Throwable
     */
    private function setReviews(int $modelId, array $data)
    {
        $user = User::findOrFail(1); // test data can be uploaded by root
        foreach ($data as $review) {
            $description = (new DescriptionSetter($review))->set();
            $review = new Review([
                'review_subject_type' => $this->parentClassName,
                'review_subject_id' => $modelId,
                'author_user_id' => $user->id,
                'review_description_id' => $description->id
            ]);
            $review->saveOrFail();
        }

    }

    /**
     * Sets basic organization data and delegates other task to sub-functions
     *
     * @param array $data
     * @return array
     * @throws \App\Exceptions\UserException
     * @throws \Throwable
     */
    private function setOrganization(array $data)
    {
        $orgPricingLogic = empty($data['pricing_logic']) ? null : Taxonomy::getTaxonomy($data['pricing_logic'],
            Config::getOrFail('taxonomies.pricing_logic'));
        $orgMarginType = empty($data['margin_type']) ? null : Taxonomy::getTaxonomy($data['margin_type'],
            Config::getOrFail('taxonomies.margin_type'));
        $orgLocation = !empty($data['location']) ? (new LocationSetter($data['location']))->set() : null;

        switch ($this->parentClassName) {
            case Ship::class:
                $model = (new OrganizationSetter([
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'short_name' => (isset($data['short_name'])) ? $data['short_name'] : null,
                    'type' => $data['type'],
                    'location' => $orgLocation ? $orgLocation->toArray() : null,
                    'parentable_type' => (isset($data['parent'])) ? ShipGroup::class : null,
                    'parent' => (isset($data['parent'])) ? $data['parent'] : null
                ]))->set(true);
                $supplier_id = null;
                break;

            case Organization::class:
                $model = (new OrganizationSetter([
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'short_name' => (isset($data['short_name'])) ? $data['short_name'] : null,
                    'type' => $data['type'],
                    'location' => $orgLocation ? $orgLocation->toArray() : null,
                    'parentable_type' => (isset($data['parent'])) ? Organization::class : null,
                    'parent' => (isset($data['parent'])) ? $data['parent'] : null
                ]))->set(true);
                $supplier_id = 401;
                break;

            case ShipGroup::class:
                $model = (new OrganizationGroupSetter([
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'location' => $orgLocation ? $orgLocation->toArray() : null,
                    'parentable_type' => (isset($data['parent'])) ? Organization::class : null,
                    'parent_id' => (isset($data['parent_id'])) ? $data['parent_id'] : null,
                    'margin_value' => (isset($data['margin_value'])) ? $data['margin_value'] : null
                ]))->set(true);
                $supplier_id = 402;
                break;

            default:
                throw new \Exception('Invalid parent class!');
        }

        $model->is_active = $data['is_active'];
        $model->pricing_logic_taxonomy_id = $orgPricingLogic ? $orgPricingLogic->id : null;
        $model->margin_type_taxonomy_id = $orgMarginType ? $orgMarginType->id : null;
        $model->supplier_id = $supplier_id;
        $model->created_at = $data['created'];
        $model->updated_at = $data['updated'];
        $model->deleted_at = $data['deleted'];
        $model->save();

        (new TestModelPropertySeeder())->seed($model, $data);

        $dateRangeRelativeIds = [];
        if (!empty($data['date_ranges'])) {
            foreach ($data['date_ranges'] as $dateRangeData) {
                $dateRangeRelativeIds[$dateRangeData['relative_id']] = DateRange::setByData($this->parentClassName,
                    $model->id, $dateRangeData)->id;
            }
        }
        if (!empty($data['descriptions'])) {
            $this->setOrganizationDescriptions($model->id, $data['descriptions']);
        }

        if (!empty($data['reviews'])) {
            $this->setReviews($model->id, $data['reviews']);
        }

        TestModelSeeder::setGallery($this->parentClassName, $model, !empty($data['media']) ? $data['media'] : []);

        return [$model, $dateRangeRelativeIds];
    }
}
