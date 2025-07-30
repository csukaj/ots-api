<?php

namespace Tests\Integration\Listeners;

use App\Events\Order\PaymentSuccessStatusEvent;
use App\Listeners\Order\BillingHandler;
use App\Services\Billing\Service as BillingService;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\OrderTestTrait;
use Tests\TestCase;

class BillingHandlerTest extends TestCase
{
    use OrderTestTrait;

    /**
     * @test
     * @runInSeparateProcess
     * 'runInSeparateProcess' needed because of session_start in invoiceagent
     * Test event listener
     */
    public function test_handle_when_service_succeeds()
    {
        $order = $this->prepareSampleOrder();

        $mockBillingService = Mockery::mock(BillingService::class);
        $mockBillingService->shouldReceive('create')->once()->andReturnSelf();
        $mockBillingService->shouldReceive('hasError')->once()->andReturn(false);
        App::instance('billing', $mockBillingService);


        (new BillingHandler())->handle(new PaymentSuccessStatusEvent($order));
        $order->refresh();
        $this->assertEquals([["date" => date('Y-m-d H:i:s'), "events" => "Billing invoice created"]], json_decode($order->status_log, true));


    }

    /**
     * @test
     * @runInSeparateProcess
     * 'runInSeparateProcess' needed because of session_start in invoiceagent
     * Test event listener
     */
    public function test_handle_when_service_fails()
    {
        $order = $this->prepareSampleOrder();

        $mockBillingService = Mockery::mock(BillingService::class);
        $mockBillingService->shouldReceive('create')->once()->andReturnSelf();
        $mockBillingService->shouldReceive('hasError')->once()->andReturn(true);
        App::instance('billing', $mockBillingService);

        (new BillingHandler())->handle(new PaymentSuccessStatusEvent($order));
        $order->refresh();
        $this->assertEquals([["date" => date('Y-m-d H:i:s'), "events" => "Billing creation failed"]], json_decode($order->status_log, true));


    }
}