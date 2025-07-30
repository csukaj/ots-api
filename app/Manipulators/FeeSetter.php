<?php

namespace App\Manipulators;

use App\AgeRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Fee;
use App\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Manipulator to create a new Fee
 * instance after the supplied data passes validation
 */
class FeeSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'product_id' => null,
        'age_range_id' => null,
        'net_price' => null,
        'rack_price' => null,
        'margin_type_taxonomy_id' => null,
        'margin_value' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'rack_price' => 'sometimes|numeric|nullable',
        'net_price' => 'sometimes|numeric|nullable',
    ];

    /**
     * Constructs Setter and validates input data
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $product = $this->getProduct($this->attributes['product_id']);
        $ageRange = $this->getAgeRange($product, isset($attributes['age_range']) ? $attributes['age_range'] : null);
        if ($ageRange) {
            $this->attributes['age_range_id'] = $ageRange->id;
        }
    }

    /**
     * @return Fee
     * @throws \Throwable
     */
    public function set(): Fee
    {
        $attributes = [
            'product_id' => $this->attributes['product_id'],
            'age_range_id' => $this->attributes['age_range_id']
        ];
        $fee = Fee::createOrRestore($attributes, $this->attributes['id']);
        $fee->fill($this->attributes)->saveOrFail();
        return $fee;
    }

    /**
     * @param int $productId
     * @return Product
     * @throws UserException
     */
    private function getProduct(int $productId): Product
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new UserException('Missing product');
        }
        return $product;
    }

    /**
     * @param Product $product
     * @param string|null $ageRangeName
     * @return AgeRange|null
     * @throws UserException
     * @throws \Exception
     */
    private function getAgeRange(Product $product, string $ageRangeName = null)
    {
        if (empty($ageRangeName)) {
            if ($product->type_taxonomy_id == Config::getOrFail('taxonomies.product_types.personal_fee')) {
                throw new UserException('Missing age range name for personal fee');
            } else {
                return null;
            }
        }
        try {
            $existing = AgeRange::findByNameOrFail($ageRangeName, Product::class, $product->id);
        } catch (ModelNotFoundException $ex) {
            throw new UserException('Missing age range');
        }
        return $existing;
    }
}
