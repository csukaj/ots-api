<?php

namespace App\Events\Order;

use App\Http\Requests\EmbeddedOrderSendRequest;

class ClosedStatusEvent extends BaseOrderStatusEvent
{
    public function __construct(EmbeddedOrderSendRequest $request, $site = '')
    {
        parent::__construct($request->model, $site);
    }

}
