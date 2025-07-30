<?php

namespace App\Listeners\Order;

use App\Services\OrderStatusLogger;

class BillingHandler
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  PaymentFailedStatusEvent $event
     * @return void
     */
    public function handle($event)
    {
        $request = isset($event->request) ? $event->request : $event;
        $orderId = $request['id'];

        $billing = app('billing')->create($orderId);

        if ($billing->hasError()) {
            $event = 'Billing creation failed';
        } else {
            $event = 'Billing invoice created';
        }
        (new OrderStatusLogger((object)['id' => $request['id']]))->addLog([
            'date' => date('Y-m-d H:i:s'),
            'events' => $event
        ]);

    }

}