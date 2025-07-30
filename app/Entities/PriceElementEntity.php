<?php

namespace App\Entities;

use App\PriceElement;

class PriceElementEntity extends Entity
{
    protected $model;

    public function __construct(PriceElement $priceElement)
    {
        parent::__construct($priceElement);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id,
            'date_range_id' => $this->model->date_range_id,
            'meal_plan' => ($this->model->modelMealPlan) ? $this->model->modelMealPlan->mealPlan->name->name : null,
            'rack_price' => $this->model->rack_price
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'admin':
                    $return['price_id'] = $this->model->price_id;
                    $return['net_price'] = $this->model->net_price;
                    $return['margin_type'] = $this->model->marginType ? $this->model->marginType->name : null;
                    $return['margin_value'] = $this->model->margin_value;
                    break;
            }
        }

        return $return;
    }
}