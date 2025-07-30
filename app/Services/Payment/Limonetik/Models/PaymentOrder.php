<?php

namespace App\Services\Payment\Limonetik\Models;

use App\Order;

/**
 * PaymentOrder model
 *
 * Based on: https://backoffice.limonetikqualif.com/API/Help.aspx?name=PaymentOrderV40
 *
 * @package App\Services\Payment\Limonetik\Model
 */
class PaymentOrder extends AbstractModel
{
    /**
     * @var Order instance
     */
    protected $order;

    /**
     * @var string The merchant identifier in the Limonetik platform.
     */
    protected $merchantId = '';

    /**
     * @var string The payment page unique identifier in the Limonetik platform.
     */
    protected $paymentPageId = '';

    /**
     * @var float The amount of the order. Format is two decimal digits after the point (eg: 5.00 for 5 euros).
     */
    protected $amount = 0.00;

    /**
     * @var string Currency; default: EUR (limonetik accept currently only EUR - 2018.05.30.)
     */
    protected $currency = 'EUR';

    /**
     * @var array MerchantUrls
     */
    protected $merchantUrls = [];

    /**
     * @var object MerchantOrder instance
     */
    protected $merchantOrder = null;

    /**
     * PaymentOrder constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;

        $this->setAmount();
    }

    /**
     * Set the merchant identifier.
     *
     * @param string $merchantId
     */
    public function setMerchantId(string $merchantId): self
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     * Set the payment page identifier
     *
     * @param string $paymentPageId
     */
    public function setPaymentPageId(string $paymentPageId): self
    {
        $this->paymentPageId = $paymentPageId;
        return $this;
    }

    /**
     * Set the currency
     *
     * @param string $currency
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Set merchant urls
     *
     * @param array $merchantUrls
     */
    public function setMerchantUrls(array $merchantUrls): self
    {
        $this->merchantUrls = $merchantUrls;
        return $this;
    }

    /**
     * Set the payment amount by the order items
     *
     * @param array $orderItems Array of App\OrderItem
     */
    public function setAmountByOrderItems(array $orderItems): self
    {
        $total = 0;
        foreach ($orderItems as $item) {
            $total += $item->price;
        }

        $this->amount = number_format($total, 2);
        return $this;
    }

    /**
     * Set the amount
     *
     * @param float $amount
     */
    protected function setAmount(): self
    {
        $this->amount = number_format($this->order->itemsTotalGross(), 2);
        return $this;
    }
}