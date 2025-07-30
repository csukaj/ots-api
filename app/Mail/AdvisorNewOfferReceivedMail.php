<?php

namespace App\Mail;

use App\Order;

class AdvisorNewOfferReceivedMail extends TemplatedMail
{

    public $templateId = 6;
    private $order;

    /**
     * Create a new message instance.
     *
     * @param object $config
     * @param Order $order
     * @param string $language
     */
    public function __construct($config, Order $order, string $language)
    {
        parent::__construct($config,$language);
        $this->order = $order;
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
