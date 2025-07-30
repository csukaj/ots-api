<?php

namespace Tests\Integration\Services;

use App\Events\Order\PaymentFailedStatusEvent;
use App\Events\Order\PaymentSuccessStatusEvent;
use App\Order;
use App\OrderItem;
use App\Payment;
use App\Services\Payment\Limonetik\Service as LimonetikService;
use App\Services\Payment\Manipulators\PaymentSetter;
use App\Services\Payment\Service as PaymentService;
use App\Supplier;
use Mockery;
use Tests\OrderTestTrait;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use OrderTestTrait;

    private function prepareMockLimonetikService($successful = true, $returnSelfs = [])
    {
        $mockLimonetikService = Mockery::mock(LimonetikService::class);
        $mockLimonetikService->shouldReceive('setLogger')->once()->andReturnNull();
        $mockLimonetikService->shouldReceive('hasError')->once()->andReturn(!$successful);
        if (!$successful) {
            $mockLimonetikService->shouldReceive('getErrors')->once()->with('json')->andReturn('["mockerrorobject"]');
            $mockLimonetikService->shouldReceive('getErrors')->once()->withNoArgs()->andReturn(['mockerrorobject']);
        }
        foreach ($returnSelfs as $methodName) {
            $mockLimonetikService->shouldReceive($methodName)->once()->andReturnSelf();
        }
        return $mockLimonetikService;
    }

    /**
     * @param OrderItem $orderItem
     * @param int $supplierId
     */
    private function setSupplierForOrderItem(OrderItem $orderItem, int $supplierId)
    {
        $productableModel = $orderItem->productableModel();
        $productableId = $productableModel->id;
        $productableClass = $orderItem->productableType();
        $product = $productableClass::findOrFail($productableId);
        if (!$product->supplier_id) {
            $product->supplier_id = $supplierId;
            $product->saveOrFail();
        }
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_write_log()
    {
        $mockLimonetikService = $this->prepareMockLimonetikService(false, ['create']);

        $sampleOrder = $this->prepareSampleOrder(true);
        $token = $sampleOrder->token;
        $service = new PaymentService($sampleOrder, config('payment'));
        $service->setService($mockLimonetikService);
        $service->create($token);
        $this->assertTrue($service->hasError());

        $configFileContent = \file(config('payment.log'), FILE_IGNORE_NEW_LINES);
        $loggedDate = date('Y-m-d H:i:s');

        $expectedLogs = [
            '[' . $loggedDate . '] payment.INFO: [CREATE][START] [OrderId: ' . $sampleOrder->id . '] [] []',
            '[' . $loggedDate . '] payment.ERROR: ["mockerrorobject"] [] []',
        ];
        $actualLogs = array_values(preg_grep('/' . $loggedDate . '/', $configFileContent));
        $this->assertEquals($expectedLogs, $actualLogs);

    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_load_order_by_token()
    {
        $sampleOrder = $this->prepareSampleOrder(true);
        $token = $sampleOrder->token;
        $service = new PaymentService($sampleOrder, config('payment'));

        $badNumericToken = 1234;
        $order = $service->loadOrderByToken($badNumericToken);
        $this->assertTrue($service->hasError());
        $this->assertEquals(['message' => 'Order with "' . $badNumericToken . '" token is not found', 'data' => []], $service->getError());
        $this->assertNull($order);

        $fakeToken = $token . 'notExists';
        $service->flushErrors();
        $order = $service->loadOrderByToken($fakeToken);
        $this->assertTrue($service->hasError());
        $this->assertEquals(['message' => 'Order with "' . $fakeToken . '" token is not found', 'data' => []], $service->getError());
        $this->assertNull($order);

        $service->flushErrors();
        $order = $service->loadOrderByToken($token);
        $this->assertFalse($service->hasError());
        $this->assertEmpty($service->getError());
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($sampleOrder->id, $order->id);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_create_when_limonetik_service_fails()
    {
        $mockLimonetikService = $this->prepareMockLimonetikService(false, ['create']);

        $sampleOrder = $this->prepareSampleOrder(true);
        $token = $sampleOrder->token;
        $service = new PaymentService($sampleOrder, config('payment'));
        $service->setService($mockLimonetikService);


        //hibás token check
        $service->create($token . 'notExists');
        $this->assertTrue($service->hasError());

        $service->flushErrors();
        $service->create($token);

        //orer payment closed = false;
        $sampleOrder->refresh();
        $this->assertFalse($sampleOrder->payment_closed);

        // check limonetik service has errors
        $this->assertTrue($service->hasError());
        $this->assertEquals(['message' => 'mockerrorobject', 'data' => []], $service->getError());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_create_when_limonetik_service_succeeds()
    {
        $mockLimonetikResponse = [
            "paymentOrderId" => $this->faker->randomNumber(8),
            "paymentPageUrl" => $this->faker->url,
            "requestId" => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            "returnCode" => 1000,
            "returnMessage" => "Success"
        ];
        $mockLimonetikService = $this->prepareMockLimonetikService(true, ['create']);
        $mockLimonetikService->shouldReceive('getResult')->andReturn($mockLimonetikResponse);

        $sampleOrder = $this->prepareSampleOrder(true);
        $service = new PaymentService($sampleOrder, config('payment'));
        $service->setService($mockLimonetikService);
        $token = $sampleOrder->token;

        $service->create($token);

        //assert that order payment closed = false;
        $sampleOrder->refresh();
        $this->assertFalse($sampleOrder->payment_closed);

        // assert that payment is stored;
        $lastPayment = Payment::orderBy('id', 'DESC')->first();
        $this->assertArraySubset(
            [
                'order_id' => $sampleOrder->id,
                'request_id' => $mockLimonetikResponse['requestId'],
                'payment_order_id' => $mockLimonetikResponse['paymentOrderId'],
                'status_log' => '{"' . date('Y-m-d H:i:s') . '":{"status":"Created"}}'
            ],
            $lastPayment->attributesToArray()
        );

        // check result
        $this->assertArraySubset($service->getResult(), $mockLimonetikResponse);
    }


    /**
     * @test
     */
    public function it_can_details()
    {
        //`details` is a developer-only function
        $mockLimonetikResponse = ['paymentOrder' => ['Id' => $this->faker->numberBetween(100000000000, 999999999999), 'Status' => 'FakeStatus']];
        $mockLimonetikService = $this->prepareMockLimonetikService(true, ['detailByPaymentOrderId']);
        $mockLimonetikService->shouldReceive('getResult')->andReturn($mockLimonetikResponse);

        $order = $this->prepareSampleOrder(true);
        $service = new PaymentService($order, config('payment'));
        $service->setService($mockLimonetikService);
        $token = $order->token;
        $payment = (new PaymentSetter([
            'order_id' => $order->id,
            'request_id' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999)
        ]))->set();

        $service->details($token);

        //check payment status updated
        $payment->refresh();
        $this->assertEquals('{"' . date('Y-m-d H:i:s') . '":{"status":"FakeStatus"}}', $payment->status_log);

        // check result
        $this->assertArraySubset($mockLimonetikResponse, $service->getResult());

    }

    /**
     * @test
     */
    public function it_can_status()
    {
        $mockPaymentService = Mockery::mock(PaymentService::class)->makePartial();
        $mockPaymentService->shouldReceive('details')->once()->andReturnSelf();
        $this->assertInstanceOf(PaymentService::class, $mockPaymentService->status(str_random(32)));
    }

    /**
     * @test
     */
    public function it_can_pay_when_limonetik_service_fails()
    {
        $mockLimonetikResponse = [];
        $mockLimonetikService = $this->prepareMockLimonetikService(false, ['pay']);
        $mockLimonetikService->shouldReceive('getResult')->andReturn($mockLimonetikResponse);

        $order = $this->prepareSampleOrder(true);

        $this->setSupplierForOrderItem($order->items[0], Supplier::first()->id);

        $this->expectsEvents(PaymentFailedStatusEvent::class);

        $service = new PaymentService($order, config('payment'));
        $service->setService($mockLimonetikService);
        $token = $order->token;
        $payment = (new PaymentSetter([
            'order_id' => $order->id,
            'request_id' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999)
        ]))->set();

        $service->pay($token);

        // check limonetik service has errors
        $this->assertTrue($service->hasError());
        $this->assertEquals(['message' => 'mockerrorobject', 'data' => []], $service->getError());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_pay_when_limonetik_service_succeeds()
    {
        $mockLimonetikResponse = [
            "paymentOrderId" => $this->faker->randomNumber(8),
            "requestId" => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            "returnCode" => 1000,
            "returnMessage" => "Success"
        ];
        $mockLimonetikService = $this->prepareMockLimonetikService(true, ['pay']);
        $mockLimonetikService->shouldReceive('getResult')->andReturn($mockLimonetikResponse);

        $order = $this->prepareSampleOrder(true);

        $this->setSupplierForOrderItem($order->items[0], Supplier::first()->id);

        $this->expectsEvents(PaymentSuccessStatusEvent::class);

        $token = $order->token;
        $payment = (new PaymentSetter([
            'order_id' => $order->id,
            'request_id' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999)
        ]))->set();

        $paymentCountBefore = Payment::where('order_id', $order->id)->count();

        $service = new PaymentService($order, config('payment'));
        $service->setService($mockLimonetikService);
        $service->pay($token);
        $this->assertFalse($service->hasError());

        //stored payment
        $orderPayments = Payment::where('order_id', $order->id)->orderBy('id', 'DESC')->get();
        $actualPayment = $orderPayments->first();
        $this->assertCount($paymentCountBefore + 1, $orderPayments);
        $this->assertEquals($order->id, $actualPayment->order_id);
        $this->assertEquals($payment->id, $actualPayment->parent_id);

        $order->refresh();
        $this->assertTrue($order->payment_closed);
    }

    /**
     * @test
     */
    public function it_can_charge_when_limonetik_service_fails()
    {
        $mockLimonetikResponse = [];
        $mockLimonetikService = $this->prepareMockLimonetikService(false, ['charge']);
        $mockLimonetikService->shouldReceive('getResult')->andReturn($mockLimonetikResponse);

        $order = $this->prepareSampleOrder(true);
        $supplierId = Supplier::first()->id;

        $this->setSupplierForOrderItem($order->items[0], $supplierId);

        $token = $order->token;
        $payment = (new PaymentSetter([
            'order_id' => $order->id,
            'request_id' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999)
        ]))->set();

        $subPayment = (new PaymentSetter([
            'order_id' => $order->id,
            //'request_id' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999),
            'parent_id' => $payment->id,
            'supplier_id' => $supplierId
        ]))->set();

        $service = new PaymentService($order, config('payment'));
        $service->setService($mockLimonetikService);
        $service->charge($token);

        // check limonetik service has errors
        $this->assertTrue($service->hasError());
        $this->assertEquals(['message' => 'mockerrorobject', 'data' => []], $service->getError());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_charge_when_limonetik_service_succeeds()
    {
        $mockLimonetikResponse = [
            "paymentOrderId" => $this->faker->randomNumber(8),
            "requestId" => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            "returnCode" => 1000,
            "returnMessage" => "Success"
        ];
        $mockLimonetikService = $this->prepareMockLimonetikService(true, ['charge']);
        $mockLimonetikService->shouldReceive('getResult')->andReturn($mockLimonetikResponse);
        $supplierId = Supplier::first()->id;

        $order = $this->prepareSampleOrder(true);

        $this->setSupplierForOrderItem($order->items[0], $supplierId);

        $token = $order->token;
        $payment = (new PaymentSetter([
            'order_id' => $order->id,
            'request_id' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999)
        ]))->set();
        $subPayment = (new PaymentSetter([
            'order_id' => $order->id,
            //'request_id' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999),
            'parent_id' => $payment->id,
            'supplier_id' => $supplierId
        ]))->set();

        $service = new PaymentService($order, config('payment'));
        $service->setService($mockLimonetikService);
        $service->charge($token);
        $this->assertFalse($service->hasError());

    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_notification()
    {
        $order = $this->prepareSampleOrder(true);
        $payment = (new PaymentSetter([
            'order_id' => $order->id,
            'request_id' => $this->faker->regexify('[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{8}'),
            'payment_order_id' => $this->faker->numberBetween(100000000000, 999999999999)
        ]))->set();

        $mockLimonetikResponse = ['paymentOrder' => [
            'Id' => $this->faker->numberBetween(100000000000, 999999999999),
            'Status' => 'FakeStatus'
        ]];
        $mockLimonetikService = $this->prepareMockLimonetikService(true, ['detailByPaymentOrderId']);
        $mockLimonetikService->shouldReceive('getResult')->andReturn($mockLimonetikResponse);

        $service = new PaymentService($order, config('payment'));
        $service->setService($mockLimonetikService);
        $service->notification(['o' => $order->token]);
        $this->assertFalse($service->hasError());

        $expectedResponse = ['orderId' => $order->id,
            'paymentOrderId' => $mockLimonetikResponse['paymentOrder']['Id'],
            'status' => $mockLimonetikResponse['paymentOrder']['Status'],
            'paymentOrder' => $mockLimonetikResponse['paymentOrder']];
        $this->assertEquals($expectedResponse, $service->getResult());

        //check payment status updated
        $payment->refresh();
        $this->assertEquals('{"' . date('Y-m-d H:i:s') . '":{"status":"FakeStatus"}}', $payment->status_log);
    }

}