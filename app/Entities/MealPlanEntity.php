<?php

namespace App\Entities;

use App\MealPlan;

class MealPlanEntity extends Entity
{

    protected $model;

    public function __construct(MealPlan $mealPlan)
    {
        parent::__construct($mealPlan);
    }

    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'name' => $this->model->name->name
        ];
    }

}
