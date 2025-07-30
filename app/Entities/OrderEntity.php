<?php

namespace App\Entities;

use App\Order;

class OrderEntity extends Entity
{
    /**
     * @var Order
     */
    protected $model;

    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = $this->model->attributesToArray();
        $return['status'] = ($this->model->statusTx) ? $this->model->statusTx->name : null;
        $return['billing_type'] = $this->model->billingType->name;
        $return['created_at'] = $this->model->created_at->toIso8601ZuluString();
        $return['updated_at'] = $this->model->updated_at->toIso8601ZuluString();

        $return['items'] = OrderItemEntity::getCollection(
            $this->model->items()->with(['orderItemable', 'guests'])->get(),
            $additions
        );

        return $return;
    }
}

