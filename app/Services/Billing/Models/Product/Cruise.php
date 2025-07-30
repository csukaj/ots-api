<?php

namespace App\Services\Billing\Models\Product;

use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class Cruise extends ProductAbstract implements ProductInterface
{
    /**
     * Examine the language specific order details string part from language content
     *
     * @return string
     * @throws \Exception
     */
    protected function getOrderDetailsString() : string
    {
        $translations = (new TaxonomyEntity($this->orderItem->orderItemable->name))->translations();
        return languageContent($this->orderItem->order->language(), $translations);
    }
}