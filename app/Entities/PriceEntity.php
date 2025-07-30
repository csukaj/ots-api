<?php

namespace App\Entities;

use App\Price;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class PriceEntity extends Entity
{
    protected $model;

    public function __construct(Price $price)
    {
        parent::__construct($price);
    }

    public function getOrderedElements()
    {
        return $this->model->elements()
            ->select('price_elements.*')
            ->join('model_meal_plans', 'price_elements.model_meal_plan_id', '=', 'model_meal_plans.id')
            ->join('date_ranges', 'price_elements.date_range_id', '=', 'date_ranges.id')
            ->orderBy('date_ranges.from_time')
            ->orderBy('model_meal_plans.meal_plan_id')
            ->get();
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id,
            'name' => (new TaxonomyEntity($this->model->name))->getFrontendData(['translations']),
            'age_range' => $this->model->ageRange ? $this->model->ageRange->name->name : null,
            'amount' => $this->model->amount,
            'extra' => $this->model->extra,
            'elements' => PriceElementEntity::getCollection($this->getOrderedElements(), $additions)
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'admin':
                    $return['product_id'] = $this->model->product_id;
                    $return['margin_type'] = $this->model->marginType ? $this->model->marginType->name : null;
                    $return['margin_value'] = $this->model->margin_value;
                    $return['mandatory'] = $this->model->mandatory;
                    break;
            }
        }

        return $return;
    }
}
