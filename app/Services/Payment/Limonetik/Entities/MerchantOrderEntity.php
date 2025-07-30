<?php

namespace App\Services\Payment\Limonetik\Entities;

use App\Services\Payment\Limonetik\Models\CartItem;
use App\Services\Payment\Limonetik\Models\Customer;
use App\Services\Payment\Limonetik\Models\MerchantOrder;

class MerchantOrderEntity
{
    protected $merchantOrder;

    public function __construct(MerchantOrder $merchantOrder)
    {
        $this->merchantOrder = $merchantOrder;
    }

    public function getFrontendData($orderItems = []): array
    {
        $entity = [
            'Id' => $this->merchantOrder->id,
            'TotalAmount' => $this->merchantOrder->totalAmount,
            'Currency' => $this->merchantOrder->currency
        ];

        $customerModel = new Customer($this->merchantOrder->order);
        $customerEntity = new CustomerEntity($customerModel, $this->merchantOrder->locale);
        $entity['Customer'] = $customerEntity->getFrontendData();

        if (!empty($orderItems)) {
            $cartItems = CartItem::allByOrderItems($orderItems);
        } else {
            $cartItems = CartItem::allByOrder($this->merchantOrder->order);
        }

        $entity['CartItems'] = CartItemEntity::getCollection($cartItems);

        return $entity;
    }
}