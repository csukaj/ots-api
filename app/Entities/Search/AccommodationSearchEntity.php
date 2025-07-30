<?php

namespace App\Entities\Search;

use App\Accommodation;
use App\Caches\AccommodationIdsCache;
use App\Device;
use App\Entities\AccommodationEntity;
use App\Entities\DeviceEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Island;
use App\MealPlan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use stdClass;
use function json_encode;

class AccommodationSearchEntity extends AbstractSearchEntity
{

    protected $parameters = [
        'interval' => null,
        'organizations' => null,
        'islands' => null,
        'meal_plans' => null,
        'usages' => null,
        'booking_date' => null,
        'wedding_date' => null,
        'cart_summary' => null,
        'returning_client' => null
    ];
    protected $hasValidInterval = false;

    public function setParameters($parameters): self
    {
        if (!is_array($parameters) || empty($parameters)) {
            return $this;
        }

        parent::setParameters($parameters);

        if (!empty($parameters['organizations'])) {
            $this->validateAccommodations($parameters['organizations']);
            $this->parameters['organizations'] = $parameters['organizations'];
        }

        if (!empty($parameters['islands'])) {
            $this->validateIslands($parameters['islands']);
            $this->parameters['islands'] = $parameters['islands'];
        }

        if (!empty($parameters['meal_plans'])) {
            $this->validateMealPlans($parameters['meal_plans']);
            $this->parameters['meal_plans'] = $parameters['meal_plans'];
        }

        return $this;
    }

    /**
     * @param array $mealPlans
     * @throws UserException
     */
    protected function validateMealPlans(array $mealPlans)
    {
        $numMealPlans = count($mealPlans);
        $mealPlanIds = MealPlan::getMealPlanIds();
        for ($i = 0; $i < $numMealPlans; $i++) {
            if (!in_array($mealPlans[$i], $mealPlanIds)) {
                throw new UserException('Invalid meal plan!');
            }
        }
    }

    /**
     * @param array $islands
     * @throws UserException
     */
    protected function validateIslands(array $islands)
    {
        $islandIds = Island::getIslandIds();
        foreach ($islands as $island) {
            if (!in_array($island, $islandIds)) {
                throw new UserException('Invalid island!');
            }
        }
    }

    /**
     * @param array $accommodations
     * @throws UserException
     */
    protected function validateAccommodations(array $accommodations)
    {
        $accommodationIds = (new AccommodationIdsCache())->getValues();
        foreach ($accommodations as $accommodation) {
            if (!in_array($accommodation, $accommodationIds)) {
                throw new UserException('Invalid organization!');
            }
        }
    }

    /**
     * @param $bookingDate
     * @throws UserException
     */
    public function setBookingDateForTests($bookingDate){
        $this->validateDate($bookingDate);
        $this->parameters['booking_date'] = $bookingDate;
    }

    public function deleteCache(int $id)
    {
        $key = 'accommodation#' . $id;
        Cache::forget($key);
    }

    /**
     * @param int $id
     * @param bool $forceRefresh
     * @return array
     * @throws \Exception
     */
    public function getSerializedOrganization(int $id, bool $forceRefresh= false): array{
        $key = 'accommodation#' . $id;
        $cached = null;
        if(!$forceRefresh) {
            $cached = Cache::get($key);
        }
        if(!$cached) {
            $accommodation = Accommodation::find($id);
            if($accommodation) {
                $cached = (new AccommodationEntity($accommodation))->getFrontendData(['frontend', 'info']);
            }
            Cache::forever($key, $cached);
        }
        return $cached;
    }

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $accommodations = [];
        $searchResult = $this->getAccommodationDataByParameters();
        $additions[] = 'info';

        foreach ($searchResult as $searchResultItem) {
            $accommodationEntity = $this->getSerializedOrganization($searchResultItem->id);
            $resultRooms = $this->hasValidInterval ? \json_decode($searchResultItem->result_usages, true) : [];
            $accommodations[$searchResultItem->id] = [
                'info' => $accommodationEntity,
                'results' => (isset($resultRooms['results'])) ? $resultRooms['results'] : [],
                'best_price' => $this->hasValidInterval ? $this->calculateBestPrice($resultRooms['results']) : []
            ];
            if (in_array('availability', $additions)) {
                $accommodations[$searchResultItem->id]['availability'] = (isset($resultRooms['availability'])) ? $resultRooms['availability'] : [];
            }
        }
        return $accommodations;
    }

    /**
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    protected function getAccommodationDataByParameters()
    {
        $parametersJSON = $this->buildParametersJSON();

        $query = DB::table('organizations AS o')->distinct();
        $query->where('o.type_taxonomy_id', Config::getOrFail('taxonomies.organization_types.accommodation.id'));
        if(!$this->showInactive) {
            $query->whereRaw('o.is_active');
        }
        $query->whereNull('o.deleted_at');
        $query->selectRaw("
                o.id,
                get_result_rooms(o.id, TEXT '{$parametersJSON}') AS result_usages
            ");

        if (!empty($this->parameters['organizations'])) {
            $query->whereIn('o.id', $this->parameters['organizations']);
        }

        return $query->get()->filter(function ($value, $key) {
            return !is_null($value->result_usages);
        })->values();
    }

    /**
     * @param $results
     * @return stdClass
     * @throws \Exception
     */
    protected function calculateBestPrice($results)
    {
        $usagePrices = [];
        $bestDevices = [];

        for ($requestIndex = 0; $requestIndex < count($results); $requestIndex++) {
            $usagePrices[$requestIndex] = [];
            foreach ($results[$requestIndex] as $device) {
                foreach ($device['prices'] as $priceData) {
                    if (
                        empty($usagePrices[$requestIndex]) ||
                        $priceData['discounted_price'] < $usagePrices[$requestIndex]['discounted_price'] ||
                        (
                            $priceData['discounted_price'] == $usagePrices[$requestIndex]['discounted_price'] &&
                            $priceData['meal_plan_id'] > $usagePrices[$requestIndex]['meal_plan_id']
                        )
                    ) {
                        $usagePrices[$requestIndex] = $priceData;
                        $bestDevices[$requestIndex] = (new DeviceEntity(Device::findOrFail($device['device_id'])))->getFrontendData();
                    }
                }
            }
        }

        $bestPrice = [
            'discounted_price' => 0,
            'original_price' => 0,
            'total_discount' => [
                'value' => 0,
                'percentage' => 0
            ],
            'devices' => $bestDevices,
            'meal_plan' => null
        ];
        foreach ($usagePrices as $requestIndex => $priceElement) {
            $bestPrice['discounted_price'] += $priceElement['discounted_price'];
            $bestPrice['original_price'] += $priceElement['original_price'];
            $bestPrice['meal_plan'] = $priceElement['meal_plan'];
        }

        if ($bestPrice['original_price'] != 0) {
            $bestPrice['total_discount']['value'] = $bestPrice['original_price'] - $bestPrice['discounted_price'];
            $bestPrice['total_discount']['percentage'] = round($bestPrice['total_discount']['value'] / $bestPrice['original_price'] * 100,
                2);
        }

        return $bestPrice ?: new stdClass();
    }
}
