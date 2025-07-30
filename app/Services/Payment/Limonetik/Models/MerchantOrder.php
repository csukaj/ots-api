<?php

namespace App\Services\Payment\Limonetik\Models;

use App\Order;

/**
 * MerchantOrder Model
 *
 * Based on: https://backoffice.limonetikqualif.com/API/Help.aspx?name=PaymentOrderV40
 *
 * @package App\Services\Payment\Limonetik\Model
 */
class MerchantOrder extends AbstractModel
{
    /**
     * @var Order instance
     */
    public $order;

    /**
     * @var int Merchant Order Id
     */
    protected $id;

    /**
     * @var float Total amount
     */
    protected $totalAmount;

    /**
     * @var float VAT amount
     */
    protected $vatAmount;

    /**
     * @var string Currency; default: EUR (limonetik accept currently only EUR - 2018.05.30.)
     */
    protected $currency = 'EUR';

    /**
     * @var string Site language - when the purchase happend
     */
    protected $language = 'hu';

    /**
     * @var string Locale; eg.: "hu-HU"
     */
    protected $locale;

    /**
     * @var Customer object
     */
    protected $customer;

    /**
     * PaymentOrder constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->locale = $order->locale();

        $this->setLanguage();
        $this->setId();
        $this->setTotalAmount();
    }

    protected function setLanguage()
    {
        $this->language = getLanguageBySite($this->order->site);
    }

    protected function setId()
    {
        $this->id = $this->order->id;
    }

    protected function setTotalAmount()
    {
        $this->totalAmount = number_format($this->order->itemsTotalGross(), 2);
    }
}