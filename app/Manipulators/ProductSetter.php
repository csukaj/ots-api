<?php

namespace App\Manipulators;

use App\Facades\Config;
use App\Product;
use App\Traits\HardcodedIdSetterTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new Product
 * instance after the supplied data passes validation
 * @todo Use elsewhere than TestOrganizationSeeder
 */
class ProductSetter extends BaseSetter
{
    use HardcodedIdSetterTrait;

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'productable_id' => null,
        'productable_type' => null,
        'type_taxonomy_id' => null,
        'margin_type_taxonomy_id' => null,
        'margin_value' => null,
        'name_description_id' => null
    ];
    private $nameDescription = null;

    /**
     * Constructs Setter and validates input data
     * @param array $attributes
     * @throws \Exception
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (isset($attributes['type'])) {
            $this->attributes['type_taxonomy_id'] = Taxonomy::getTaxonomy($attributes['type'],
                Config::getOrFail('taxonomies.product_type'))->id;
        }

        if (isset($attributes['name_description'])) {
            $this->nameDescription = $attributes['name_description'];
        }
    }

    /**
     * Creates new Model or updates if exists
     * @param bool $hardcodedId use supplied id?
     * @return Product
     * @throws \Throwable
     */
    public function set(bool $hardcodedId = false): Product
    {

        $update = (!$hardcodedId && $this->attributes['id']);

        if ($update) {
            $product = Product::findOrFail($this->attributes['id']);
            if ($this->nameDescription) {
                $this->attributes['name_description_id'] = (
                new DescriptionSetter($this->nameDescription, $product->name_description_id)
                )->set()->id;
            }
        } else {
            $attributes = [
                'productable_id' => $this->attributes['productable_id'],
                'productable_type' => $this->attributes['productable_type'],
                'type_taxonomy_id' => $this->attributes['type_taxonomy_id'],
            ];
            $product = Product::createOrRestore($attributes);
            if ($this->nameDescription) {
                $this->attributes['name_description_id'] = (new DescriptionSetter($this->nameDescription))->set()->id;
            }
        }

        if ($hardcodedId && $this->attributes['id']) {
            $product->id = $this->attributes['id'];
        }

        $product->type_taxonomy_id = $this->attributes['type_taxonomy_id'];
        $product->margin_type_taxonomy_id = $this->attributes['margin_type_taxonomy_id'];
        $product->margin_value = $this->attributes['margin_value'];
        $product->name_description_id = $this->attributes['name_description_id'];
        $product->saveOrFail();

        if ($hardcodedId && $this->attributes['id']) {
            $this->updateAutoIncrement($product);
        }

        return $product;
    }
}
