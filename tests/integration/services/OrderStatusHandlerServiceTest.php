<?php

namespace Tests\Integration\Services;

use App\Events\Order\ClosedStatusEvent;
use App\Events\Order\ConfirmedStatusEvent;
use App\Events\Order\NewOrderStatusEvent;
use App\Events\Order\NewUniqueProductOrderStatusEvent;
use App\Events\Order\OfferUnderProcessingStatusEvent;
use App\Events\Order\PayingStatusEvent;
use App\Events\Order\PaymentFailedStatusEvent;
use App\Events\Order\PaymentSuccessStatusEvent;
use App\Events\Order\WaitingForOfferStatusEvent;
use Tests\OrderTestTrait;
use Tests\TestCase;

class OrderStatusHandlerServiceTest extends TestCase
{

    use OrderTestTrait;

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_NEW_ORDER()
    {
        $request = $this->createRequest('POST', '', '/test', ['CONTENT_TYPE' => 'application/json'], [], [], [], false);
        $this->assertEventFired(NewOrderStatusEvent::class, $request);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_WAITING_FOR_OFFER()
    {
        $order = $this->prepareSampleOrder();
        $eventClass = WaitingForOfferStatusEvent::class;
        $this->assertEventFired($eventClass, null, $order);
        $order->refresh();
        $this->assertStatusUpdated($order, $eventClass);
        $this->assertLogWritten($order, $eventClass);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_OFFER_UNDER_PROCESSING()
    {
        $order = $this->prepareSampleOrder();
        $request = $this->createRequest('POST', json_encode(['model' => ['id' => $order->id]]));
        $eventClass = OfferUnderProcessingStatusEvent::class;
        $this->assertEventFired($eventClass, $request);
        $order->refresh();
        $this->assertStatusUpdated($order, $eventClass);
        $this->assertLogWritten($order, $eventClass);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_CONFIRMED()
    {
        $order = $this->prepareSampleOrder();
        $request = $this->createRequest('POST', json_encode(['model' => ['id' => $order->id]]));
        $eventClass = ConfirmedStatusEvent::class;
        $this->assertEventFired($eventClass, $request);
        $order->refresh();
        $this->assertStatusUpdated($order, $eventClass);
        $this->assertLogWritten($order, $eventClass);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_CLOSED()
    {
        $order = $this->prepareSampleOrder();
        $request = $this->createRequest('POST', json_encode(['model' => ['id' => $order->id]]));
        $eventClass = ClosedStatusEvent::class;
        $this->assertEventFired($eventClass, $request);
        $order->refresh();
        $this->assertStatusUpdated($order, $eventClass);
        $this->assertLogWritten($order, $eventClass);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_NEW_UNIQUE_PRODUCT_ORDER()
    {
        $order = $this->prepareSampleOrder();
        $eventClass = NewUniqueProductOrderStatusEvent::class;
        $this->assertEventFired($eventClass, $order, $order);
        $order->refresh();
        $this->assertStatusUpdated($order, $eventClass);
        $this->assertLogWritten($order, $eventClass);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_PAYING()
    {
        $order = $this->prepareSampleOrder();
        $request = $this->createRequest('POST', json_encode(['model' => ['id' => $order->id]]), '/test', ['CONTENT_TYPE' => 'application/json'], [], [], [], false);
        $eventClass = PayingStatusEvent::class;
        $this->assertEventFired($eventClass, $request);
        $order->refresh();
        $this->assertStatusUpdated($order, $eventClass);
        $this->assertLogWritten($order, $eventClass);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_PAYMENT_SUCCESS()
    {
        $order = $this->prepareSampleOrder();
        $eventClass = PaymentSuccessStatusEvent::class;
        $this->assertEventFired($eventClass, $order, $order);
        $order->refresh();
        $this->assertStatusUpdated($order, $eventClass);
        $this->assertLogWritten($order, $eventClass);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_step_status_when_target_status_is_PAYMENT_FAILED()
    {
        $order = $this->prepareSampleOrder();
        $eventClass = PaymentFailedStatusEvent::class;
        $this->assertEventFired($eventClass, $order, $order);
        $order->refresh();
        $this->assertStatusUpdated($order, $eventClass);
        $this->assertLogWritten($order, $eventClass);
    }


}