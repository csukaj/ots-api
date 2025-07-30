<?php

namespace App\Services\Billing\Models\Product;

use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class ShipGroup extends ProductAbstract implements ProductInterface
{
    /**
     * Examine the language specific order details string part from language content
     *
     * @return string
     * @throws \Exception
     */
    protected function getOrderDetailsString() : string
    {
        $translations = (new DescriptionEntity($this->orderItem->orderItemable->name))->getFrontendData();
        $orderDetailsString = languageContent($this->orderItem->order->language(), $translations);

        return $orderDetailsString;
    }
}