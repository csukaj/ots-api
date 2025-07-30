<?php

namespace App\Events\Order;

use App\Order;

class WaitingForOfferStatusEvent extends BaseOrderStatusEvent
{
    public function __construct(Order $request, $site = '')
    {
        parent::__construct($request, $site);
    }
}
