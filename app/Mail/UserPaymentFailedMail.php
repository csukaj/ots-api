<?php

namespace App\Mail;

use App\Order;

class UserPaymentFailedMail extends TemplatedMail
{
    public $templateId = 5;
    public $order;

    /**
     * Create a new message instance.
     *
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        parent::__construct($order, $this->order->language());
    }

    protected function getPlaceHolderDictionary(): array
    {
        return  [
            '{{username}}' => $this->order->fullName(),
            '{{orderId}}' => $this->order->id,
            '{{transactionId}}' => $this->order->payment->payment_order_id
        ];
    }
}
