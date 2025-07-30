<?php

namespace App\Console\Commands\TestModelSeeder;

use App\Console\Commands\TestModelPropertySeeder;
use App\Cruise;
use App\CruiseDescription;
use App\CruiseDevice;
use App\DateRange;
use App\Facades\Config;
use App\Manipulators\CruiseSetter;
use App\Manipulators\LocationSetter;
use App\Manipulators\ScheduleSetter;
use App\Schedule;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Class to seed a accommodation test data
 */
class TestCruiseSeeder
{

    public function __construct()
    {
        //
    }

    /**
     * Seed a cruise with all associated data, including price modifiers
     *
     * @param array $data
     */
    public function seed(array $data)
    {
        list($cruise, $dateRangeRelativeIds) = $this->setCruise($data);
        (new TestPriceModifierSeeder())->seed(Cruise::class, $cruise->id, $data, $dateRangeRelativeIds);
    }


    /**
     * Sets basic organization data and delegates other task to subfunctions
     *
     * @param array $data
     * @return array
     */
    private function setCruise(array $data)
    {
        $crPricingLogic = empty($data['pricing_logic']) ? null : Taxonomy::getTaxonomy($data['pricing_logic'],
            Config::getOrFail('taxonomies.pricing_logic'));
        $crMarginType = empty($data['margin_type']) ? null : Taxonomy::getTaxonomy($data['margin_type'],
            Config::getOrFail('taxonomies.margin_type'));
        $crLocation = !empty($data['location']) ? (new LocationSetter($data['location']))->set() : null;

        $model = (new CruiseSetter([
            'id' => $data['id'],
            'name' => $data['name'],
            'location' => $crLocation ? $crLocation->toArray() : null,
            'ship_company_id' => (isset($data['ship_company_id'])) ? $data['ship_company_id'] : null,
            'ship_group_id' => (isset($data['ship_group_id'])) ? $data['ship_group_id'] : null,
            'itinerary_id' => (isset($data['itinerary_id'])) ? $data['itinerary_id'] : null
        ]))->set(true);

        $model->is_active = $data['is_active'];
        $model->pricing_logic_taxonomy_id = $crPricingLogic ? $crPricingLogic->id : null;
        $model->margin_type_taxonomy_id = $crMarginType ? $crMarginType->id : null;
        $model->supplier_id = 402;
        $model->created_at = $data['created'];
        $model->updated_at = $data['updated'];
        $model->deleted_at = $data['deleted'];
        $model->save();

        (new TestModelPropertySeeder())->seed($model, $data);

        $dateRangeRelativeIds = [];
        if (!empty($data['date_ranges'])) {
            foreach ($data['date_ranges'] as $dateRangeData) {
                $dateRangeRelativeIds[$dateRangeData['relative_id']] = DateRange::setByData(
                    Cruise::class, $model->id, $dateRangeData
                )->id;
            }
        }
        if (!empty($data['descriptions'])) {
            TestModelSeeder::setModelDescriptions(CruiseDescription::class, $model->id, 'cruise_id',
                'cruise_descriptions', $data['descriptions']);
        }

        if (!empty($data['schedule'])) {
            foreach ($data['schedule'] as $scheduleData) {
                $this->setSchedule($model->id, $scheduleData);
            }
        }

        TestModelSeeder::setAgeRanges(Cruise::class, $model->id, $data['age_ranges']);

        if (!empty($data['cruise_devices'])) {
            foreach ($data['cruise_devices'] as $cruiseDeviceData) {
                $this->setCruiseDevice($model, $cruiseDeviceData, $dateRangeRelativeIds);
            }
        }

        return [$model, $dateRangeRelativeIds];
    }

    public function setSchedule(int $modelId, array $data): Schedule
    {
        $data['cruise_id'] = $modelId;
        return (new ScheduleSetter($data))->set();
    }

    private function setCruiseDevice(Cruise $cruise, array $data, array $dateRangeRelativeIds): CruiseDevice
    {
        $deviceId = $cruise->shipGroup->devices()->orderBy('id')->get()->splice($data['device_relative_id'] - 1, 1)->first()->id;
        $cruiseDevice = CruiseDevice::createOrRestore([
            'cruise_id' => $cruise->id,
            'device_id' => $deviceId
        ]);

        $productSeederParams = [
            'modelType' => Cruise::class,
            'modelId' => $cruise->id,
            'productableType' => CruiseDevice::class,
            'productableId' => $cruiseDevice->id,
        ];
        (new TestProductSeeder())->seed($productSeederParams, $dateRangeRelativeIds, $data);

        return $cruiseDevice;
    }
}
