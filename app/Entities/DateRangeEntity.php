<?php

namespace App\Entities;

use App\DateRange;
use App\ModelMealPlan;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class DateRangeEntity extends Entity
{
    protected $model;

    public function __construct(DateRange $dateRange)
    {
        parent::__construct($dateRange);
    }

    /**
     * @param array $additions
     * @return array
     * @throws \Exception
     */
    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'date_rangeable_type' => $this->model->date_rangeable_type,
            'date_rangeable_id' => $this->model->date_rangeable_id,
            'name' => $this->model->name ? (new DescriptionEntity($this->model->name))->getFrontendData() : null,
            'from_date' => $this->model->from_time,
            'to_date' => $this->model->to_time,
            'type' => $this->model->type->name,
            'margin_type' => $this->model->marginType ? $this->model->marginType->name : null,
            'margin_value' => $this->model->margin_value,
            'minimum_nights' => $this->model->minimum_nights ? $this->model->minimum_nights : null,
            'meal_plans' => ModelMealPlan::getNames($this->model->modelMealPlans)
        ];
    }
}