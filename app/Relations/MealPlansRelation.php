<?php

namespace App\Relations;

use App\Entities\MealPlanEntity;
use App\MealPlan;

/**
 * Relation for displaying Meal Plans
 */
class MealPlansRelation extends Relation {
    
    protected $type = self::TYPE_ONE_TO_MANY;
    protected $format = self::FORMAT_CSV;

    /**
     * Format data for displaying on frontend
     * 
     * @return array
     */
    public function getFrontendData() {
        return [
            'type' => $this->type,
            'format' => $this->format,
            'options' => MealPlanEntity::getCollection(MealPlan::all())
        ];
    }

}
