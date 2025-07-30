<?php

namespace Tests\Integration\Listeners;

use App\Events\Order\ClosedStatusEvent;
use App\Events\Order\ConfirmedStatusEvent;
use App\Events\Order\NewOrderStatusEvent;
use App\Events\Order\NewUniqueProductOrderStatusEvent;
use App\Events\Order\OfferUnderProcessingStatusEvent;
use App\Events\Order\PayingStatusEvent;
use App\Events\Order\PaymentFailedStatusEvent;
use App\Events\Order\PaymentSuccessStatusEvent;
use App\Events\Order\WaitingForOfferStatusEvent;
use App\Listeners\Order\BillingHandler;
use App\Listeners\Order\NewOrderHandler;
use App\Listeners\Order\NotificationSender;
use App\Listeners\Order\PayHandler;
use App\Providers\EventServiceProvider;
use Tests\TestCase;

class EventListenerHandlerBindingsTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     * Test event listener
     */
    public function testBindings()
    {
        $expected = [
            NewOrderStatusEvent::class => [NewOrderHandler::class],
            WaitingForOfferStatusEvent::class => [NotificationSender::class],
            OfferUnderProcessingStatusEvent::class => [],
            ConfirmedStatusEvent::class => [NotificationSender::class],
            ClosedStatusEvent::class => [NotificationSender::class],
            NewUniqueProductOrderStatusEvent::class => [NotificationSender::class],
            PayingStatusEvent::class => [PayHandler::class],
            PaymentSuccessStatusEvent::class => [NotificationSender::class, BillingHandler::class],
            PaymentFailedStatusEvent::class => [NotificationSender::class],
        ];
        $provider = new EventServiceProvider($this->app);
        $this->assertEquals($expected, $provider->listens());

    }
}