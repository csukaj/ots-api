<?php

namespace Tests\Integration\Listeners;

use App\Events\Order\NewOrderStatusEvent;
use App\Events\Order\WaitingForOfferStatusEvent;
use App\Facades\Config;
use App\Listeners\Order\NewOrderHandler;
use App\Order;
use Tests\OrderTestTrait;
use Tests\TestCase;

class NewOrderHandlerTest extends TestCase
{
    use OrderTestTrait;

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     * Test event listener
     * @throws \Exception
     * @throws \Throwable
     */
    public function testHandle()
    {
        $contentObject = json_decode(file_get_contents(__DIR__ . '/testdata/NewOrderHandlerTestRequest.json'));
        $contentObject->site = 'http://ots.local';
        $content = \json_encode($contentObject);
        $orderSendRequest = $this->createRequest(
            'POST', $content, '/order', ['CONTENT_TYPE' => 'application/json',
                'Referer'=>'http://ots.local/my-holiday'], [], [], [], false
        );
        $orderSendRequest->headers->set('Referer','http://ots.local/my-holiday');
        $listener = new NewOrderHandler();
        $listener->handle(new NewOrderStatusEvent($orderSendRequest));

        $orderCreated = $listener->getOrder();

        // order saved with items
        $this->assertInstanceOf(Order::class, $orderCreated);
        $this->assertTrue($orderCreated->exists());

        $orderCreated->refresh();
        $this->assertNotEmpty($orderCreated->items()->get());
        //log written
        $this->assertLogWritten($orderCreated,NewOrderStatusEvent::class,true);

        //order status is updated to WAITING_FOR_OFFER
        $this->assertEquals(Config::getOrFail('taxonomies.order_statuses.waiting_for_offer.id'),$orderCreated->status_taxonomy_id);
        $this->assertLogWritten($orderCreated,WaitingForOfferStatusEvent::class);

    }
}