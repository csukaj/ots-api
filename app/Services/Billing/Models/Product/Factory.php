<?php

namespace App\Services\Billing\Models\Product;

use App\OrderItem;

class Factory
{
    /**
     * @param OrderItem $orderItem
     * @return ProductInterface
     * @throws \Exception
     */
    static public function getModel(OrderItem $orderItem) : ProductInterface
    {
        switch ($orderItem->productableType())
        {
            case 'App\ShipGroup':
                $product = new ShipGroup($orderItem);
                break;
            case 'App\Accommodation':
            case 'App\Organization':
                $product = new Accomodation($orderItem);
                break;
            case 'App\Cruise':
                $product = new Cruise($orderItem);
                break;
            case 'App\UniqueProduct':
                $product = new Unique($orderItem);
                break;
            default :
                $message = 'Unknown productable type: "' . $orderItem->productableType() . '"';
                throw new \Exception($message);
        }

        return $product;
    }

}