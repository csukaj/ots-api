<?php

namespace App\Mail;

use App\Order;

class AdminPaymentFailedMail extends TemplatedMail
{
    public $templateId = 10;
    public $order;

    /**
     * Create a new message instance.
     *
     * @param object $config
     * @param Order $order
     */
    public function __construct($config, Order $order)
    {
        $this->order = $order;
        parent::__construct($config, $this->order->language());
    }

    protected function getPlaceHolderDictionary(): array
    {
        return  [
            '{{username}}' => $this->config->username,
            '{{adminOrderDetailsLink}}' => $this->config->adminOrderDetailsLink,
            '{{orderId}}' => $this->order->id,
            '{{orderCreatedAt}}' => $this->order->created_at,
            '{{customerName}}' => $this->order->fullName(),
            '{{customerEmail}}' => $this->order->email
        ];
    }
}
