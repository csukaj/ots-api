<?php

namespace App\Services\Payment\Limonetik\Entities;

use App\Services\Payment\Limonetik\Models\Cancel as CancelModel;

class CancelEntity
{
    protected $cancelModel;

    public function __construct(CancelModel $cancelModel)
    {
        $this->cancelModel = $cancelModel;
    }

    public function getFrontendData()
    {
        $entity = [
            'CancelAmount' => $this->cancelModel->cancelAmount,
            'Currency' => $this->cancelModel->currency
        ];

        $properties = [
            'PaymentOrderId' => 'paymentOrderId',
            'MerchantOrderId' => 'merchantOrderId',
            'MerchantId' => 'merchantId',
            'MerchantOperationId' => 'merchantOperationId'
        ];

        foreach ($properties as $property => $key)
        {
            if ($this->cancelModel->$key != '')
            {
                $entity[$property] = $this->cancelModel->$key;
            }
        }

        return $entity;
    }
}