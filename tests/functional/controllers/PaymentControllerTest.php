<?php

namespace Tests\Functional\Controllers;

use App\Services\Payment\Service as PaymentService;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\OrderTestTrait;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use OrderTestTrait;
    static public $setupMode = self::SETUPMODE_ALWAYS;

    private function prepareMockedService($successful = true, $returnSelfs = [])
    {
        // this time we use 3rd party services, so we mock it to test our controller
        $mockedService = Mockery::mock(PaymentService::class);
        $mockedService->shouldReceive('hasError')->once()->andReturn(!$successful);
        if (!$successful) {
            $mockedService->shouldReceive('getErrorMessages')->once()->withNoArgs()->andReturn(['mockerrorobject']);
        }
        foreach ($returnSelfs as $methodName) {
            $mockedService->shouldReceive($methodName)->once()->andReturnSelf();
        }
        App::instance('payment', $mockedService);
        return $mockedService;
    }

    /**
     * @test
     */
    public function it_can_create_when_service_succeeds()
    {
        $expectedData = [];
        $mockPaymentService = $this->prepareMockedService(true, ['create']);
        $mockPaymentService->shouldReceive('getResult')->once()->andReturn($expectedData);

        $payload = ['token' => str_random(32)];
        list(, , $response) = $this->httpApiRequest('/payment/create', 'POST', [], $payload, true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => true, 'data' => $expectedData]);
    }

    /**
     * @test
     */
    public function it_can_create_when_service_fails()
    {
        $this->prepareMockedService(false, ['create']);

        $payload = ['token' => str_random(32)];
        list(, , $response) = $this->httpApiRequest('/payment/create', 'POST', [], $payload, true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => false, 'data' => ['mockerrorobject']]);
    }

    /**
     * @test
     */
    public function it_can_notification_when_service_succeeds()
    {
        $this->prepareMockedService(true, ['notification']);

        list(, , $response) = $this->httpApiRequest('/payment/notification?token=' . str_random(32), 'GET', [], [], true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => true]);
    }

    /**
     * @test
     */
    public function it_can_notification_when_service_fails()
    {
        $mockedService = Mockery::mock(PaymentService::class);
        $mockedService->shouldReceive('notification')->once()->andReturnSelf();
        $mockedService->shouldReceive('hasError')->once()->andReturn(true);

        App::instance('payment', $mockedService);

        list(, , $response) = $this->httpApiRequest('/payment/notification?token=' . str_random(32), 'GET', [], [], true);
        
        $response
            ->assertStatus(400)
            ->assertExactJson(['success' => true]);
    }

    /**
     * @test
     */
    public function it_can_not_pay_when_order_not_found()
    {
        $payload = ['token' => str_random(32)];
        $expectedData = ["redirectToMainPage" => true];
        $mockPaymentService = Mockery::mock(PaymentService::class);
        $mockPaymentService->shouldReceive('loadOrderByToken')->once()->andReturn(null);
        App::instance('payment', $mockPaymentService);


        list(, , $response) = $this->httpApiRequest('/payment/pay', 'POST', [], $payload, true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => false, 'data' => $expectedData]);

    }

    /**
     * @test
     */
    public function it_can_not_pay_when_order_payment_closed()
    {
        $payload = ['token' => str_random(32)];
        $expectedData = ["redirectToMainPage" => true];
        //payment closed @ order
        $order = $this->prepareSampleOrder(true);
        $order->payment_closed = true;
        $order->saveOrFail();
        $mockPaymentService = Mockery::mock(PaymentService::class);
        $mockPaymentService->shouldReceive('loadOrderByToken')->once()->andReturn($order);
        App::instance('payment', $mockPaymentService);

        list(, , $response) = $this->httpApiRequest('/payment/pay', 'POST', [], $payload, true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => false, 'data' => $expectedData]);
    }

    /**
     * @test
     */
    public function it_can_not_pay_when_service_fails()
    {
        $order = $this->prepareSampleOrder(true);
        $mockPaymentService = $this->prepareMockedService(false, ['pay']);
        $mockPaymentService->shouldReceive('loadOrderByToken')->once()->andReturn($order);

        $payload = ['token' => str_random(32)];
        list(, , $response) = $this->httpApiRequest('/payment/pay', 'POST', [], $payload, true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => false, 'data' => ['mockerrorobject']]);
    }

    /**
     * @test
     */
    public function it_can_pay_when_service_succeeds()
    {
        $order = $this->prepareSampleOrder(true);
        $mockPaymentService = $this->prepareMockedService(true, ['pay']);
        $mockPaymentService->shouldReceive('loadOrderByToken')->once()->andReturn($order);

        $payload = ['token' => str_random(32)];
        list(, , $response) = $this->httpApiRequest('/payment/pay', 'POST', [], $payload, true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => true, 'data' => []]);
    }

    /**
     * @test
     */
    public function it_can_status_when_service_succeeds()
    {
        $expectedData = [
            'orderId' => $this->faker->randomNumber,
            'paymentOrderId' => $this->faker->randomNumber,
            'status' => $this->faker->word
        ];
        $mockPaymentService = $this->prepareMockedService(true, ['status']);
        $mockPaymentService->shouldReceive('getResult')->once()->andReturn($expectedData);

        $payload = ['token' => str_random(32)];
        list(, , $response) = $this->httpApiRequest('/payment/status', 'POST', [], $payload, true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => true, 'data' => $expectedData]);
    }

    /**
     * @test
     */
    public function it_can_status_when_service_fails()
    {
        $mockedService = Mockery::mock(PaymentService::class);
        $mockedService->shouldReceive('status')->once()->andReturnSelf();
        $mockedService->shouldReceive('hasError')->once()->andReturn(true);

        App::instance('payment', $mockedService);

        $payload = ['token' => str_random(32)];
        list(, , $response) = $this->httpApiRequest('/payment/status', 'POST', [], $payload, true);
        
        $response
            ->assertStatus(200)
            ->assertExactJson(['success' => false, 'data' => []]);
    }
}