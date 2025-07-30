<?php

namespace App\Manipulators;

use App\Device;
use App\Exceptions\UserException;
use App\Price;
use App\Product;

/**
 * Manipulator to create a new Price
 * instance after the supplied data passes validation
 */
class PriceSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     */
    protected $attributes = [
        'id' => null,
        'product_id' => null,
        'age_range_id' => null,
        'name_taxonomy_id' => null,
        'amount' => null,
        'margin_type_taxonomy_id' => null,
        'margin_value' => null,
        'extra' => null,
        'mandatory' => null,
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'id' => 'sometimes|integer|nullable',
        'product_id' => 'required|integer',
        'age_range_id' => 'sometimes|integer',
        'name_taxonomy_id' => 'required|integer',
        'amount' => 'sometimes|integer', // validated after this validation based on productable type
        'margin_type_taxonomy_id' => 'integer',
        'margin_value' => 'numeric|nullable',
        'extra' => 'boolean',
        'mandatory' => 'boolean',
    ];

    /**
     * PriceSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $product = Product::findOrFail($this->attributes['product_id']);
        if ($product->productable_type == Device::class && (!$this->attributes['age_range_id'] || !$this->attributes['amount'])) {
            throw new UserException('Amount or age range missing for Price!');
        }
    }

    /**
     * @return Price
     * @throws \Throwable
     */
    public function set(): Price
    {
        $attributes = [
            'product_id' => $this->attributes['product_id'],
            'age_range_id' => $this->attributes['age_range_id'],
            'name_taxonomy_id' => $this->attributes['name_taxonomy_id']
        ];
        $price = Price::createOrRestore($attributes, $this->attributes['id']);
        $price->fill($this->attributes)->saveOrFail();
        return $price;
    }

}
