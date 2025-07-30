<?php

namespace Tests\Integration\Listeners;

use App\Events\Order\PayingStatusEvent;
use App\Http\Requests\OrderSendRequest;
use App\Listeners\Order\PayHandler;
use Tests\TestCase;

class PayHandlerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;


    /**
     * @test
     * Test event listener
     */
    public function testHandle()
    {

        $listener = new PayHandler();
        $listener->handle(new PayingStatusEvent(new OrderSendRequest()));
        $this->markTestSkipped('testable code block is empty, so we can\'t test anything');
    }
}