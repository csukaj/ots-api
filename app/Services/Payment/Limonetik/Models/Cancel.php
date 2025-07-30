<?php

namespace App\Services\Payment\Limonetik\Models;

class Cancel extends AbstractModel
{
    public function __construct(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (!property_exists($this, $property)) {
                throw new \Exception('The following property is not exist in ' . __CLASS__ . ' Model: "' . $property . '"');
            }
            $this->$property = $value;
        }
    }

    /**
     * @var string
     */
    protected $paymentOrderId;

    /**
     * @var float
     */
    protected $cancelAmount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $merchantOrderId;

    /**
     * @var string
     */
    protected $merchantId;

    /**
     * @var string
     */
    protected $merchantOperationId;
}