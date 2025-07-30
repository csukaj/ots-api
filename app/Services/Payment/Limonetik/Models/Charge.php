<?php

namespace App\Services\Payment\Limonetik\Models;

class Charge extends AbstractModel
{
    public function __construct(array $properties=[])
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new \Exception('The following property is not exist in ' . __CLASS__ . ' Model: "' . $property . '"');
            }
            $this->$property = $value;
        }
    }

    public $paymentOrderId;

    public $chargeAmount;

    public $currency;
}