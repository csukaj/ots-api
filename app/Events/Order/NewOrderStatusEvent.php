<?php

namespace App\Events\Order;

use App\Http\Requests\OrderSendRequest;

class NewOrderStatusEvent extends BaseOrderStatusEvent
{
    public function __construct(OrderSendRequest $request, $site = '')
    {
        parent::__construct($request, $site);
    }
}
