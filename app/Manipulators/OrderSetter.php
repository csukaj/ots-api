<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Order;
use App\OrderItem;

class OrderSetter extends BaseSetter
{
    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'first_name' => null,
        'last_name' => null,
        'company_name' => null,
        'tax_number' => null,
        'nationality' => null,
        'email' => null,
        'telephone' => null,
        'site' => null,
        'status_taxonomy_id' => null,
        'status_log' => null,
        'token' => null,
        'token_created_at' => null,
        'payment_closed' => null,
        'billing_country' => null,
        'billing_zip' => null,
        'billing_settlement' => null,
        'billing_address' => null,
        'billing_type_taxonomy_id' => null
    ];

    private $whitelistAttributes = [
        'id',
        'first_name',
        'last_name',
        'company_name',
        'tax_number',
        'email',
        'telephone',
        'billing_country',
        'billing_zip',
        'billing_settlement',
        'billing_address'
    ];

    private $order;

    private $orderItems = [];

    private $useWhitelistAttributes = false;

    public function __construct(array $attributes, bool $useWhitelistAttributes = false)
    {
        $this->useWhitelistAttributes = $useWhitelistAttributes;
        parent::__construct($attributes); // loadAttributes is overloaded here...
    }

    /**
     * @return Order
     * @throws UserException
     */
    public function set() : Order
    {
        $this->saveOrder();
        $this->saveOrderItems();

        return $this->order;
    }

    /**
     * @throws UserException
     */
    private function saveOrder()
    {
        if (!empty($this->attributes['id'])) {
            $this->order = Order::findOrFail($this->attributes['id']);
        } else {
            $this->order = new Order();
        }
        try {
            $this->order->fill($this->attributes)->saveOrFail();
        } catch (\Throwable $e) {
            throw new UserException('Failed to save Order');
        }
    }

    /**
     * @throws UserException
     */
    private function saveOrderItems()
    {
        foreach ($this->orderItems as $orderItem) {
            $itemSetter = new OrderItemSetter($orderItem);
            if (empty($this->attributes['id'])) {
                $itemSetter->setOrderId($this->order->id);
            }
            try {
                $itemSetter->set();
            } catch (\Throwable $e) {
                throw new UserException('Failed to save OrderItem');
            }
        }
    }

    /**
     * Load attributes to $this->attributes.
     * It load automatically all properties but if the $this->useWhitelistAttributes is TRUE
     * then it load only the attributes on $this->whitelistAttributes
     *
     * @param array $attributes
     */
    protected function loadAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                if ($this->useWhitelistAttributes) {
                    if (in_array($key, $this->whitelistAttributes)) {
                        $this->attributes[$key] = $value;
                    } else {
                        unset($this->attributes[$key]);
                    }
                } else {
                    $this->attributes[$key] = $value;
                }
            }
        }
        if (!empty($attributes['order_items'])) {
            if ($this->useWhitelistAttributes) {
                if (in_array('order_items', $this->whitelistAttributes)) {
                    $this->orderItems = $attributes['order_items'];
                }
            } else {
                $this->orderItems = $attributes['order_items'];
            }
        }
    }
}
