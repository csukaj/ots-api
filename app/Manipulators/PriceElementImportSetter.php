<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\MealPlan;
use App\PriceElement;

class PriceElementImportSetter extends BaseSetter
{
    protected $attributes = [
        'enabled' => null,
        'price_id' => null,
        'meal_plan' => null,
        'model_meal_plan_id' => null,
        'date_range_id' => null,
        'net_price' => null,
        'rack_price' => null,
        'margin_value' => 0,
        'margin_type_taxonomy_id' => null
    ];

    /**
     * PriceElementImportSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
       parent::__construct($attributes);

        //todo: with validator facade
        $required = [
            'price_id' => 'Price Id',
            'model_meal_plan_id' => 'Model Meal Plan Id',
            'date_range_id' => 'Date Range Id',
            'margin_type_taxonomy_id' => 'Margin type taxonomy id'
        ];
        foreach ($required as $key => $fieldName) {
            if (!is_numeric($attributes[$key])) {
                throw new UserException($fieldName . " is required and it must be number.");
            }
        }

        if (!is_bool($attributes['enabled'])) {
            throw new UserException("Enabled is required.");
        }

        if (is_null($attributes['net_price']) && is_null($attributes['rack_price'])) {
            throw new UserException("Net price and Rack price together can not be null.");
        }

        if (!in_array($attributes['meal_plan'], MealPlan::getMealPlanNames())) {
            throw new UserException("Meal plan is not valid.");
        }
    }

    /**
     * @return bool
     * @throws UserException
     * @throws \Throwable
     */
    public function set(): bool
    {
        if (!$this->attributes['enabled']) {
            $element = PriceElement::where([
                'price_id' => $this->attributes['price_id'],
                'model_meal_plan_id' => $this->attributes['model_meal_plan_id'],
                'date_range_id' => $this->attributes['date_range_id']
            ])->first();
            if($element) {
                $element->delete();
            }
            return true;
        }
        $element = (new PriceElementSetter($this->attributes))->set();
        if (is_null($element->margin_value)) {
            $element->margin_value = 0;
            $element->saveOrFail();
        }
        return true;
    }
}
