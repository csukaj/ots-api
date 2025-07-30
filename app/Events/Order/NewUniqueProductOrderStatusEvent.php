<?php

namespace App\Events\Order;

use App\Order;

class NewUniqueProductOrderStatusEvent extends BaseOrderStatusEvent
{
    public function __construct(Order $request, $site = '')
    {
        parent::__construct($request, $site);
    }

}
