<?php

namespace App\Manipulators;

use App\Cart;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\UniqueProduct;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class CartSetter extends BaseSetter
{
    protected $id;

    protected $cart;

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'type' => null,
        'first_name' => null,
        'last_name' => null,
        'site' => null,
        'company_name' => null,
        'tax_number' => null,
        'country' => null,
        'zip' => null,
        'city' => null,
        'address' => null,
        'email' => null,
        'phone' => null,
        'unique_products' => []
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = ['id' => 'integer|nullable'];

    private $saveType;

    /**
     * CartSetter constructor.
     * @param array $attributes
     * @param string $saveType
     * @throws UserException
     */
    public function __construct(array $attributes, string $saveType)
    {
        if (!in_array($saveType, ['draft', 'sent'])) {
            throw new UserException('Bad save type!');
        }
        $this->saveType = $saveType;

        parent::__construct($attributes);

        if (!isset($attributes['unique_products'])) {
            throw new UserException('unique products missing');
        }
    }

    /**
     * @return Cart
     * @throws \Exception
     * @throws \Throwable
     */
    public function set(): Cart
    {
        $this->saveCart();
        $this->deleteAssignedUniqueProducts();
        $this->saveUniqueProducts();

        return $this->cart;
    }

    /**
     * @return Cart
     * @throws \Throwable
     */
    private function saveCart(): Cart
    {
        if (!empty($this->attributes['id'])) {
            $this->cart = Cart::findOrFail($this->attributes['id']);
        } else {
            $this->cart = new Cart($this->attributes);
        }

        $this->cart->fill($this->attributes);
        $this->cart->status_taxonomy_id = $this->getStatusTaxonomyId();
        $this->cart->billing_type_taxonomy_id = $this->getBillingTypeTaxonomyId();
        $this->cart->saveOrFail();

        return $this->cart;
    }

    /**
     * @throws \Throwable
     */
    private function saveUniqueProducts()
    {
        foreach ($this->attributes['unique_products'] as $product) {
            if(isset($product['id'])){
                $uniqueProduct = UniqueProduct::withTrashed()->findOrNew($product['id']);
                if($uniqueProduct->trashed()){
                    $uniqueProduct->restore();
                }
            }else{
                $uniqueProduct = new UniqueProduct();
            }
            $uniqueProduct->fill($product);
            $uniqueProduct->cart_id = $this->cart->id;
            $uniqueProduct->supplier_id = $this->getSupplierId($product);
            $uniqueProduct->saveOrFail();
        }
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
    private function deleteAssignedUniqueProducts()
    {
        return UniqueProduct::where('cart_id', $this->cart->id)->delete();
    }

    private function getSupplierId(array $product)
    {
        if (isset($product['supplier'][0]['id'])) {
            return $product['supplier'][0]['id'];
        } elseif (isset($product['supplier_id'])) {
            return $product['supplier_id'];
        }
        return null;
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getStatusTaxonomyId(): int
    {
        return Taxonomy::getTaxonomy($this->saveType, Config::getOrFail('taxonomies.cart_status'))->id;
    }

    /**
     * @return int
     * @throws \Exception
     */
    private function getBillingTypeTaxonomyId(): int
    {
        return Taxonomy::getTaxonomy($this->attributes['type'], Config::getOrFail('taxonomies.billing_type'))->id;
    }
}
