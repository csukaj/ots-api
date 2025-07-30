<?php

namespace App\Relations;

use App\Entities\MealPlanEntity;
use App\MealPlan;

/**
 * Relation for displaying Meal Plans (one-to-one)
 */
class MealPlanRelation extends Relation {

    protected $type = self::TYPE_ONE_TO_ONE;
    protected $format = self::FORMAT_SINGLE_VALUE;

    /**
     * Format data for displaying on frontend
     * 
     * @return array
     */
    public function getFrontendData() {
        return [
            'type' => $this->type,
            'format' => $this->format,
            'options' => MealPlanEntity::getCollection(MealPlan::all()),
            'emptyItemName' => '(on meal plan)'
        ];
    }

}
