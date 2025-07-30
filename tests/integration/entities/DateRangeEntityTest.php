<?php

namespace Tests\Integration\Entities;

use App\Entities\DateRangeEntity;
use App\ModelMealPlan;
use App\Organization;
use Tests\TestCase;

class DateRangeEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models_and_entity() {
        $organization = Organization::findOrFail(1);
        $dateRange = $organization->dateRanges[0];
        return [$organization, $dateRange, (new DateRangeEntity($dateRange))];
    }

    /**
     * @test
     */
    function a_date_range_has_basic_data() {
        list(, $dateRange, $dateRangeEntity) = $this->prepare_models_and_entity();
        $frontendData = $dateRangeEntity->getFrontendData();

        $this->assertEquals($dateRange->id, $frontendData['id']);
        $this->assertEquals($dateRange->name->description, $frontendData['name']['en']);
        $this->assertEquals($dateRange->from_time, $frontendData['from_date']);
        $this->assertEquals($dateRange->to_time, $frontendData['to_date']);
        $this->assertEquals($dateRange->type->name, $frontendData['type']);
        $this->assertEquals($dateRange->marginType ? $dateRange->marginType->name : null, $frontendData['margin_type']);
        $this->assertEquals($dateRange->margin_value, $frontendData['margin_value']);

        $expectedMealPlans = $dateRange->modelMealPlans()->get()->all();
        usort($expectedMealPlans, ModelMealPlan::class.'::compareByPriority');
        
        foreach ($expectedMealPlans as $modelMealPlan) {
            $mealPlanData = array_shift($frontendData['meal_plans']);
            $this->assertEquals($modelMealPlan->mealPlan->name->name, $mealPlanData);
        }
    }
}