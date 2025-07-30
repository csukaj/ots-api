<?php

namespace App\Manipulators;

use App\DateRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\ModelMealPlan;
use App\PriceElement;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class PriceElementSetter extends BaseSetter
{
    protected $attributes = [
        'id' => null,
        'price_id' => null,
        'model_meal_plan_id' => null,
        'date_range_id' => null,
        'net_price' => null,
        'rack_price' => null,
        'margin_type_taxonomy_id' => null,
        'margin_value' => 0,
    ];

    /**
     * PriceElementSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        //TODO: validate with validator facade


        if (empty($attributes['price_id'])
            || empty($attributes['date_range_id'])
            || empty($attributes['model_meal_plan_id']) && empty($attributes['meal_plan'])
        ) {
            throw new UserException('Missing required parameter for PriceElementSetter!');
        }

        $dateRange = DateRange::findOrFail($attributes['date_range_id']);

        if (!empty($attributes['meal_plan'])) {
            $this->attributes['model_meal_plan_id'] = ModelMealPlan::findByName($attributes['meal_plan'],
                $dateRange)->id;
        }
        if (!empty($attributes['margin_type'])) {
            $this->attributes['margin_type_taxonomy_id'] = Taxonomy::getTaxonomy($attributes['margin_type'],
                Config::get('taxonomies.margin_type'))->id;
        }
    }

    /**
     * @return PriceElement
     * @throws \Throwable
     */
    public function set(): PriceElement
    {
        $attributes = [
            'price_id' => $this->attributes['price_id'],
            'model_meal_plan_id' => $this->attributes['model_meal_plan_id'],
            'date_range_id' => $this->attributes['date_range_id']
        ];
        $priceElement = PriceElement::createOrRestore($attributes, $this->attributes['id']);
        $priceElement->fill($this->attributes)->saveOrFail();
        $priceElement->recalculatePrices()->saveOrFail();
        return $priceElement;
    }
}
