<?php

namespace App\Manipulators;

use App\OrderItem;

class OrderItemSetter extends BaseSetter
{
    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'order_id' => null,
        'order_itemable_type' => null,
        'order_itemable_id' => null,
        'from_date' => null,
        'to_date' => null,
        'amount' => null,
        'meal_plan_id' => null,
        'order_itemable_index' => null,
        'price' => null,
        'margin' => null,
        'json' => null,
        'tax' => null
    ];
    private $inputAttributes;

    private $orderId;

    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
        $this->inputAttributes = $attributes;
        //@TODO: attribute validation with validator facade

        if (is_null($this->attributes['tax'])) {
            $this->attributes['tax'] = 0;
        }
    }

    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function set(): OrderItem
    {
        if ($this->orderId) {
            $this->attributes['order_id'] = $this->orderId;
        }
        if (!$this->attributes['id'] && !$this->attributes['json']) { //save only original order item data order item should never updated.
            unset($this->attributes['json']);
            $this->attributes['json'] = \json_encode($this->inputAttributes);
        }
        if (!$this->attributes['json']) {
            $this->attributes['json'] = \json_encode($this->inputAttributes);
        }
        $orderItem = ($this->attributes['id']) ? OrderItem::findOrFail($this->attributes['id']) : new OrderItem($this->attributes);
        $orderItem->fill($this->attributes)->saveOrFail();
        return $orderItem;
    }

}
