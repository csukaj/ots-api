<?php

namespace App\Entities\Search;

use App\Caches\CruiseIdsCache;
use App\Cruise;
use App\Device;
use App\Entities\CruiseEntity;
use App\Entities\DeviceEntity;
use App\Exceptions\UserException;
use App\Island;
use App\MealPlan;
use Illuminate\Support\Facades\DB;
use stdClass;
use function json_encode;

class CruiseSearchEntity extends AbstractSearchEntity
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
            $this->validateCruises($parameters['organizations']);
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
     * @param array $cruises
     * @throws UserException
     */
    protected function validateCruises(array $cruises)
    {
        $cruiseIds = (new CruiseIdsCache())->getValues();
        foreach ($cruises as $cruise) {
            if (!in_array($cruise, $cruiseIds)) {
                throw new UserException('Invalid organization!');
            }
        }
    }

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        $cruises = [];
        $searchResult = $this->getCruiseDataByParameters();
        $additions[] = 'info';
        $additions[] = 'galleries';
        $additions[] = 'ship_company';
        $additions[] = 'ship_group';

        foreach ($searchResult as $searchResultItem) {
            $cruise = Cruise::find($searchResultItem->id);
            $cruiseData = (new CruiseEntity($cruise))->getFrontendData($additions);
            $cruiseSchedules = \json_decode($searchResultItem->cruise_schedules, true);
            foreach ($cruiseSchedules as $cruiseSchedule) {
                if (!$this->hasValidInterval || !empty($cruiseSchedule['results'])) {
                    $temp = [
                        'info' => $cruiseData,
                        'schedule' => $cruiseSchedule['schedule'],
                        'results' => $cruiseSchedule['results'],
                        'best_price' => $this->hasValidInterval ? $this->calculateBestPrice($cruiseSchedule['results']) : []
                    ];
                    if (in_array('availability', $additions)) {
                        $temp['availability'] = (isset($cruiseSchedule['availability'])) ? $cruiseSchedule['availability'] : [];
                    }
                    $cruises[] = $temp;
                }
            }
        }
        return $cruises;
    }

    protected function getCruiseDataByParameters()
    {
        $parametersJSON = $this->buildParametersJSON();

        $query = DB::table('cruises AS c')->distinct();
        $query->join('organizations AS o', 'c.ship_company_id', '=', 'o.id');
        if (!$this->showInactive) {
            $query->whereRaw('c.is_active');
        }
        $query->whereNull('c.deleted_at');
        if (!$this->showInactive) {
            $query->whereRaw('o.is_active');
        }
        $query->whereNull('o.deleted_at');
        $query->selectRaw("
                c.id,
                get_result_cruises(o.id, c.id, TEXT '{$parametersJSON}') AS cruise_schedules
            ");

        return $query->get()->filter(function ($value, $key) {
            return $value->cruise_schedules != '[]';
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
