<?php

namespace App\Services\Payment\Limonetik\Entities;

use App\Services\Payment\Limonetik\Models\CartItem;

class CartItemEntity
{
    protected $cartItem;

    public function __construct(CartItem $cartItem)
    {
        $this->cartItem = $cartItem;
    }

    public function getFrontendData(): array
    {
        return [
            'Id' => $this->cartItem->id,
            'UnitPrice' => $this->cartItem->unitPrice,
            'Quantity' => $this->cartItem->quantity,
        ];
    }

    static public function getCollection(array $cartItems): array
    {
        $collection = [];

        foreach ($cartItems as $cartItem) {
            $collection[] = (new self($cartItem))->getFrontendData();
        }
        return $collection;
    }
}