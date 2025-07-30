<?php

namespace App\Services\Payment\Limonetik\Models;

use App\Order;
use App\OrderItem;

class CartItem extends AbstractModel
{
    protected $orderItem;

    public $id;

    public $unitPrice;

    public $quantity;

    /**
     * PaymentOrder constructor.
     *
     * @param OrderItem $orderItem
     */
    public function __construct(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;

        $this->setId();
        $this->setUnitPrice();
        $this->setQuantity();
    }

    protected function setId()
    {
        $this->id = $this->orderItem->id;
    }

    protected function setUnitPrice()
    {
        $this->unitPrice = $this->orderItem->price;
    }

    protected function setQuantity()
    {
        $this->quantity = $this->orderItem->amount;
    }

    static public function allByOrder(Order $order)
    {
        return self::allByOrderItems($order->items->all());
    }

    static public function allByOrderItems(array $orderItems)
    {
        $all = [];

        foreach ($orderItems as $item) {
            $all[] = new self($item);
        }

        return $all;
    }
}