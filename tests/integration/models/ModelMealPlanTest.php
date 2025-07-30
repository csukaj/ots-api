<?php

namespace Tests\Integration\Models;

use App\MealPlan;
use App\ModelMealPlan;
use Tests\TestCase;

class ModelMealPlanTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_NEVER;

    /**
     * @test
     */
    function it_can_be_sorted_by_meal_plans_priority() {
        $expectedMealPlanNames = [
            'e/p',
            'b/b',
            'h/b',
            'f/b',
            'inc'
        ];

        $mealPlanNames = [
            'inc',
            'b/b',
            'h/b',
            'f/b',
            'e/p'
        ];

        $modelMealPlans = [];

        foreach ($mealPlanNames as $mealPlanName) {
            $mealPlan = MealPlan::findByName($mealPlanName);
            $modelMealPlan = new ModelMealPlan(
                [
                    'meal_plan_id' => $mealPlan->id
                ]
            );
            $modelMealPlan->id = $this->faker->unique()->randomDigit;
            $modelMealPlans[] = $modelMealPlan;
        }

        $namesWOId = ModelMealPlan::getNames($modelMealPlans);
        $namesWId = ModelMealPlan::getNames($modelMealPlans, true);

        $this->assertEquals($expectedMealPlanNames, $namesWOId);
        $this->assertTrue($this->compareArraysValueWithOrderWithoutIndex($expectedMealPlanNames, $namesWId));
    }

    private function compareArraysValueWithOrderWithoutIndex($expectedMealPlanNames, $namesWId) {
        $countOfExpected = count($expectedMealPlanNames);
        $countOfNames = count($namesWId);

        if ($countOfExpected !== $countOfNames) return false;

        for ($i = 1; $i <= $countOfNames; $i++) {

            $expectedValue = current($expectedMealPlanNames);
            $actualValue = current($namesWId);

            if( $expectedValue !== $actualValue ) return false;

            next($expectedMealPlanNames);
            next($namesWId);
        }
        
        return true;
    }


}
