<?php

namespace App\Services\Payment\Limonetik\Models;

use App\Order;

/**
 * Customer Model class
 *
 * Based on: https://backoffice.limonetikqualif.com/API/Help.aspx?name=PaymentOrderV40
 *
 * @package App\Services\Payment\Limonetik\Model
 */
class Customer extends AbstractModel
{
    /**
     * @var Order instance
     */
    protected $order;

    /**
     * @var string Customer identifier
     */
    protected $id;

    /**
     * @var string Customer email address
     */
    protected $email;

    /**
     * @var string Customer's first name
     */
    protected $firstName;

    /**
     * @var string Customer's last name
     */
    protected $lastName;

    /**
     * PaymentOrder constructor.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;

        $this->setId();
        $this->setEmail();
        $this->setFirstName();
        $this->setLastName();
    }

    protected function setId()
    {
        $this->id = $this->order->id;
    }

    protected function setEmail()
    {
        $this->email = $this->order->email;
    }

    protected function setFirstName()
    {
        $this->firstName = $this->order->first_name;
    }

    public function setLastName()
    {
        $this->lastName = $this->order->last_name;
    }

}