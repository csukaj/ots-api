<?php

namespace App\Services\Payment\Limonetik\Entities;

use App\Services\Payment\Limonetik\Models\Charge;

class ChargeEntity
{
    protected $charge;

    public function __construct(Charge $charge)
    {
        $this->charge = $charge;
    }

    public function getFrontendData(array $orderItems)
    {
        $entity = [
            'PaymentOrderId' => $this->charge->paymentOrderId,
            'Currency' => $this->charge->currency
        ];

        $chargeAmount = 0.0;
        $commission = 0.0;
        foreach ($orderItems as $orderItem)
        {
            $chargeAmount+= $orderItem->price;
            $commission+= $orderItem->margin;
        }

        $entity['ChargeAmount'] = number_format($chargeAmount, 2);

        $entity['Fees'] = [
            [
                'Id' => 'Commission OTS',
                'Amount' => number_format($commission, 2)
            ]
        ];

        return $entity;
    }
}