<?php
namespace App\Relations;

use App\Cruise;
use App\Entities\ProductEntity;
use App\Facades\Config;
use App\Organization;
use App\OrganizationGroup;
use App\Product;
use Illuminate\Database\Eloquent\Model;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Relation for displaying Discounted Accommodatio Products
 */
class DiscountedAccommodationProductsRelation extends Relation
{

    protected $type = self::TYPE_ONE_TO_MANY;
    protected $format = self::FORMAT_CSV;
    protected $devices;

    public function __construct(Taxonomy $taxonomy, Model $model)
    {
        parent::__construct($taxonomy);
        
        switch (get_class($model)) {
            case Organization::class:
            case OrganizationGroup::class:
                $devices = $model->devices;
                break;
            case Cruise::class:
                $devices = $model->shipGroup->devices;
                break;

            default:
                $devices = [];
                break;
        }
        $this->devices = $devices;
    }

    /**
     * Format data for displaying on frontend
     * 
     * @return array
     */
    public function getFrontendData()
    {
        $products = [];
        if ($this->devices) {
            foreach ($this->devices as $device) {
                $productsOfDevice = $device
                    ->products()
                    ->where('type_taxonomy_id', Config::getOrFail('taxonomies.product_types.price_modified_accommodation'))
                    ->get()
                    ->toArray();
                $products = array_merge($products, $productsOfDevice);
            }
        }

        return [
            'type' => $this->type,
            'format' => $this->format,
            'options' => ProductEntity::getCollection(Product::hydrate($products))
        ];
    }
}
