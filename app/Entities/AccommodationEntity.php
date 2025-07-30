<?php

namespace App\Entities;

use App\Accommodation;
use App\Availability;
use App\Device;
use App\Facades\Config;
use App\HotelChain;
use App\Organization;
use App\OrganizationClassification;
use App\OrganizationMeta;
use Carbon\Carbon;
use Exception;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class AccommodationEntity extends OrganizationEntity
{

    /**
     *
     * @param Accommodation $accommodation
     * @param string $fromDate
     * @param string $toDate
     * @param string|null $productType
     */
    public function __construct(Accommodation $accommodation, string $fromDate = null, string $toDate = null, string $productType = null)
    {
        parent::__construct($accommodation, $fromDate, $toDate, $productType);
    }

    /**
     *
     * @param array $additions
     * @return array
     * @throws Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $return = parent::getFrontendData($additions);
        $parent = $this->organization->hotelChain;
        $return['parent'] = $parent && is_a($parent, HotelChain::class)
            ? (new HotelChainEntity($parent))->getFrontendData() : null;
        $return['deviceable_type'] = Organization::class;

        $infoAdditions = array_merge(['descriptions', 'properties', 'images'], $additions);
        foreach ($additions as $addition) {
            switch ($addition) {
                case 'info':
                    $return['devices'] = $this->getDevices($infoAdditions);
                    $return['location'] = $this->organization->location_id ? (new LocationEntity($this->organization->location))->getFrontendData(['frontend']) : null;
                    $return['descriptions'] = $this->getEntityDescriptionsData($this->organization->id,
                        Config::getOrFail('taxonomies.organization_description'));
                    $return['properties'] = $this->getProperties();
                    $return['galleries'] = $this->getGalleries();
                    $return['settings'] = $this->getSettings();
                    $return['age_ranges'] = AgeRangeEntity::getCollection($this->organization->ageRanges, ['frontend']);
                    $return['meal_plans'] = $this->getMealPlans();
                    $return['search_options'] = $this->getSearchOptions();
                    break;

                case 'admin_properties':
                    $return['properties'] = $this->getAdminProperties();
                    break;

                case 'galleries':
                    $return['galleries'] = $this->getGalleries();
                    break;

                case 'availability_update':
                    $return['availability_update'] = $this->getManagedAvailabilityInfo();
                    break;
                default:
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
        $classifications = [];
        $settingClassificationPaths = [
            'taxonomies.organization_properties.categories.settings.items.price_level.id',
            'taxonomies.organization_properties.categories.settings.items.stars.id',
            'taxonomies.organization_properties.categories.general.items.accommodation_category.id'
        ];
        foreach ($settingClassificationPaths as $settingIdPath) {
            $model = OrganizationClassification::getClassification(
                self::CONNECTION_COLUMN,
                $this->organization->id,
                Config::getOrFail($settingIdPath)
            );
            if (!empty($model)) {
                $classifications[] = $model;
            }
        }
        $classificationsData = OrganizationClassificationEntity::getCollection($classifications, ['frontend']);

        $settingMetaPaths = [
            'taxonomies.organization_properties.categories.general.metas.distance_from_beach.id'
        ];
        $metas = [];
        foreach ($settingMetaPaths as $settingIdPath) {
            $model = OrganizationMeta::getMeta(
                self::CONNECTION_COLUMN,
                $this->organization->id,
                Config::getOrFail($settingIdPath)
            );
            if (!empty($model)) {
                $metas[] = $model;
            }
        }
        $metasData = OrganizationMetaEntity::getCollection($metas, ['frontend']);
        return array_merge($classificationsData,$metasData);
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    private function getAdminProperties(): array
    {
        $models = [];
        $settings = [
            'taxonomies.organization_properties.categories.general.items.accommodation_category.id',
            'taxonomies.organization_properties.categories.settings.items.stars.id'
        ];
        foreach ($settings as $settingIdPath) {
            $model = OrganizationClassification::getClassification(
                self::CONNECTION_COLUMN,
                $this->organization->id,
                Config::getOrFail($settingIdPath)
            );
            if (!empty($model)) {
                $models[] = $model;
            }
        }
        return OrganizationClassificationEntity::getCollection($models, ['frontend']);
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getManagedAvailabilityInfo(){
        $channelManagerTxId = $this->organization->getChannelManagerId();
        $channelManagers = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.channel_manager.elements');
        $deviceIds = $this->organization->devices()->get()->pluck('id');
        $maxAvailabilityUpdateString = Availability
            ::where('available_type', Device::class)
            ->whereIn('available_id', $deviceIds)
            ->max('updated_at');
        $maxAvailabilityUpdate = (new Carbon($maxAvailabilityUpdateString));
        $updatedUntil = null;
        if ($channelManagerTxId == $channelManagers['Hotel Link Solutions']) {
            $toDays = Config::getOrFail('services.channel_managers.providers.hotel_link_solutions.availability_to_days');
            try {
                $updatedUntil = $maxAvailabilityUpdate->copy()->addDays($toDays)->format('Y-m-d');
            } catch (Exception $e) {
                $updatedUntil = null;
            }
        }
        return [
            'channel_manager' => $channelManagerTxId ? Taxonomy::find($channelManagerTxId)->name : null,
            'last_updated_at' => $maxAvailabilityUpdate->toIso8601ZuluString(),
            'last_updated_until' => $updatedUntil
        ];
    }
}
