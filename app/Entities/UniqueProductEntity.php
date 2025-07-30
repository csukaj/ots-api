<?php

namespace App\Entities;

use App\UniqueProduct;

class UniqueProductEntity extends Entity
{
    /**
     * @var UniqueProduct
     */
    protected $model;

    public function __construct(UniqueProduct $uniqueProduct)
    {
        parent::__construct($uniqueProduct);
    }

    public function getFrontendData(array $additions = []): array
    {
        $frontentData = [
            'id' => $this->model->id,
            'supplier_id' => $this->model->supplier_id,
            'cart_id' => $this->model->cart_id,
            'name' => $this->model->name,
            'unit' => $this->model->unit,
            'from_date' => $this->model->from_date,
            'to_date' => $this->model->to_date,
            'amount' => $this->model->amount,
            'net_price' => $this->model->net_price,
            'margin' => $this->model->margin,
            'tax' => $this->model->tax,
            'description' => $this->model->description,
            'unitPrice' => $this->model->unitPrice(),
            'sumPrice' => $this->model->sumPrice(),
        ];

        return $frontentData;
    }
}