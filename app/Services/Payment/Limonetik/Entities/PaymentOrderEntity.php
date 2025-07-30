<?php

namespace App\Services\Payment\Limonetik\Entities;

use App\Services\Payment\Limonetik\Models\PaymentOrder;
use App\Services\Payment\Limonetik\Models\MerchantOrder;

class PaymentOrderEntity
{
    protected $paymentOrder;

    protected $locale;

    public function __construct(PaymentOrder $paymentOrder)
    {
        $this->paymentOrder = $paymentOrder;
        $this->locale = $paymentOrder->locale;
    }

    public function getFrontendData(array $orderItems = []) : array
    {
        $entity = [
            'MerchantId' => $this->paymentOrder->merchantId,
            'PaymentPageId' => $this->paymentOrder->paymentPageId,
            'Amount' => $this->paymentOrder->amount,
            'Currency' => $this->paymentOrder->currency
        ];

        if (count($this->paymentOrder->merchantUrls))
        {
            $entity['MerchantUrls'] = $this->paymentOrder->merchantUrls;
        }

        $merchantOrderModel = new MerchantOrder($this->paymentOrder->order);
        $entity['MerchantOrder'] = (new MerchantOrderEntity($merchantOrderModel))->getFrontendData($orderItems);

        return $entity;
    }
}