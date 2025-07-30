<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // frontend
        \App\Events\Order\NewOrderStatusEvent::class => [
            \App\Listeners\Order\NewOrderHandler::class, // (validation), save order, set availability, auto set status: waiting for offer
        ],
        // new order status event / admin
        \App\Events\Order\WaitingForOfferStatusEvent::class => [
            \App\Listeners\Order\NotificationSender::class, //email: user(thank you, it's doing), ad(you have a new task)
        ],
        // admin
        \App\Events\Order\OfferUnderProcessingStatusEvent::class => [
        ],
        // admin
        \App\Events\Order\ConfirmedStatusEvent::class => [
            \App\Listeners\Order\NotificationSender::class, //email: user(with payment option)
        ],
        // admin
        \App\Events\Order\ClosedStatusEvent::class => [
            /*
             * BAD!!!! this creates new order.
             * we just need to set availability. (Do we need?)
             * G 180802
             * \App\Listeners\Order\NewOrderHandler::class, //set availability
             */
            \App\Listeners\Order\NotificationSender::class, //email: user(sorry, we don't able to help)
        ],
        \App\Events\Order\NewUniqueProductOrderStatusEvent::class => [
            \App\Listeners\Order\NotificationSender::class, //email: user(with payment option)
        ],
        // frontend
        \App\Events\Order\PayingStatusEvent::class => [
            \App\Listeners\Order\PayHandler::class, //limonetik; save limonetik response; auto set status: payment_success or payment_failed
        ],
        // payment
        \App\Events\Order\PaymentSuccessStatusEvent::class => [
            \App\Listeners\Order\NotificationSender::class, //email: user(payment is success and details)
            \App\Listeners\Order\BillingHandler::class  //response to user about the success payment; empty cart
        ],
        // payment
        \App\Events\Order\PaymentFailedStatusEvent::class => [
            /* @ivan @20180629 - Ha egy fizetes sikertelen, az nem szabad hogy azt jelentse, hogy egybol
             * fel is szabaditjuk az altala lefoglalt osszeget, mert lehet csak felreutotte a CVC kodot. A link, ami alapjan
             * fizet, email-be van elkuldve, ami 24 oraig el. Ezert kikommenteztem ezt, majd kell kesziteni egy cron job-ot, ami
             * visszaallitja az availablility-t!
             * // 'App\Listeners\Order\NewOrderHandler', //set availability
             */

            \App\Listeners\Order\NotificationSender::class, //email: user(payment is failed)
            //response to user about the failed payment
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher $events
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
