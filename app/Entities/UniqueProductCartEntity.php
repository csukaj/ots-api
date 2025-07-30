<?php

namespace App\Entities;

use App\Cart;

class UniqueProductCartEntity extends Entity
{
    protected $model;

    public function __construct(Cart $cart)
    {
        parent::__construct($cart);
        $this->model = $cart;
    }

    public function getFrontendData(array $additions = []): array
    {
        $frontendData = [
            'id' => $this->model->id,
            'type' => $this->model->billingType->name,
            'status' => $this->model->status->name,
            'tax_number' => $this->model->tax_number,
            'site' => $this->model->site,
            'country' => $this->model->country,
            'zip' => $this->model->zip,
            'city' => $this->model->city,
            'address' => $this->model->address,
            'email' => $this->model->email,
            'phone' => $this->model->phone,
            'created_at' => $this->model->created_at->toIso8601ZuluString()
        ];

        switch ($frontendData['type']) {
            case 'individual':
                $frontendData['first_name'] = $this->model->first_name;
                $frontendData['last_name'] = $this->model->last_name;
                break;
            case 'company':
                $frontendData['company_name'] = $this->model->company_name;
                $frontendData['tax_number'] = $this->model->tax_number;
                break;
        }

        $frontendData['unique_products'] = UniqueProductEntity::getCollection($this->model->uniqueProducts);
        $frontendData['sum'] = $this->calculateCartSumAmount($frontendData['unique_products']);

        return $frontendData;
    }

    protected function calculateCartSumAmount($uniqueProducts): int
    {
        $sum = 0;

        foreach ($uniqueProducts as $product) {
            $sum += $product['sumPrice'];
        }

        return $sum;
    }
}