<?php

namespace App\Services\Billing\Models\Product;

use App\OrderItem;

interface ProductInterface
{
    public function __construct(OrderItem $orderItem);

    public function getName();

    public function getQuantity();

    public function getQuantityUnit();

    public function getUnitPrice();

    public function getVat();

    public function getNetPrice();

    public function getVatAmount();

    public function getGrossAmount();

    public function getComment();
}