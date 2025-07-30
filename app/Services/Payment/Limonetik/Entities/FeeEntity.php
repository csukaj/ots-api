<?php

namespace App\Services\Payment\Limonetik\Entities;

use App\Services\Payment\Limonetik\Models\Fee;

class FeeEntity
{
    protected $fee;

    public function __construct(Fee $fee)
    {
        $this->fee = $fee;
    }

    public function getFrontendData()
    {
        return [
            'Id' => 'OTS Commission',
            'Amount' => $this->fee->amount
        ];
    }

    public function getCollection(array $fees)
    {
        $collection = [];

        foreach ($fees as $fee)
        {
            $collection[] = (new self($fee))->getFrontendData();
        }

        return $collection;
    }
}