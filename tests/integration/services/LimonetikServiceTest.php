<?php

namespace Tests\Integration\Services;

use App\Payment;
use App\Services\Payment\Limonetik\Entities\PaymentOrderEntity;
use App\Services\Payment\Limonetik\Models\PaymentOrder;
use App\Services\Payment\Limonetik\Service as LimonetikService;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Tests\OrderTestTrait;
use Tests\TestCase;

class LimonetikServiceTest extends TestCase
{
    use OrderTestTrait;

    private $logger;

    public function setUp(): void
    {
        parent::setUp();
        $this->initLogger();
    }

    /**
     * Initialize the payment logger
     *
     * @throws \Exception
     */
    private function initLogger()
    {
        $this->app->configureMonologUsing(function ($monolog) {
            $monolog->pushHandler(new \Monolog\Handler\TestHandler());
        });
    }

    private function prepareService(GuzzleResponse $mockResponse): LimonetikService
    {
        $mockHttpClient = \Mockery::mock(HttpClient::class);
        $mockHttpClient->shouldReceive('send')->once()->andReturn($mockResponse);
        $limonetikService = new LimonetikService(config('payment.limonetik'), 'hu');
        $limonetikService->setLogger(app('log')->getMonolog());
        $limonetikService->setHttpClient($mockHttpClient);
        return $limonetikService;
    }

    private function getPayLoad(string $message)
    {
        $message = str_replace('---Request payload: ', '', $message);
        if (strpos($message, ' ###') !== false) {
            $message = substr($message, 0, strpos($message, ' ###'));
        }
        return json_decode($message, true);
    }

    private function getRawRq(string $message)
    {
        return substr($message, strpos($message, ' ###') + 5);
    }

    /**
     * @test
     */
    public function it_can_create()
    {
        $order = $this->prepareSampleOrder(true);
        $responseData = [
            'PaymentPageUrl' => $this->faker->url,
            'PaymentOrderId' => $this->faker->randomNumber,
            'RequestId' => $this->faker->randomNumber

        ];
        $mockResponse = new GuzzleResponse(200, ['Content-type: application/json'], json_encode($responseData));
        $limonetikService = $this->prepareService($mockResponse);
        $limonetikService->create($order);

        $expected = [];
        foreach ($responseData as $k => $v) {
            $expected[lcfirst($k)] = $v;
        }


        //test expected response
        $this->assertEquals($expected, $limonetikService->getResult());
        // Retrieve the records from the Monolog TestHandler
        //test request payload from log
        $records = app('log')->getMonolog()->getHandlers()[0]->getRecords();
        $payload = $this->getPayLoad($records[0]['message']);
        $paymentOrderModel = (new PaymentOrder($order))
            ->setMerchantId(config('payment.limonetik.MERCHANT_ID'))
            ->setPaymentPageId(config('payment.limonetik.PAYMENT_PAGE_ID'));

        $expectedPayLoad = ['PaymentOrder' => (new PaymentOrderEntity($paymentOrderModel))->getFrontendData()];
        $this->assertArraySubset($expectedPayLoad, $payload);
        //check if response logged
        $this->assertEquals('Response: ' . json_encode($responseData), $records[1]['message']);
    }

    /**
     * @test
     */
    public function it_can_detailByPaymentOrderId()
    {
        $responseData = [
            'PaymentOrder' => $this->faker->sentence,
        ];
        $mockResponse = new GuzzleResponse(200, ['Content-type: application/json'], json_encode($responseData));
        $limonetikService = $this->prepareService($mockResponse);
        $paymentOrderId = $this->faker->randomNumber;
        $elements = [
            'MerchantOrder' => $this->faker->word,
            'OperationsSummary' => $this->faker->word
        ];
        $limonetikService->detailByPaymentOrderId($paymentOrderId, $elements);

        $expected = [];
        foreach ($responseData as $k => $v) {
            $expected[lcfirst($k)] = $v;
        }


        //test expected response
        $this->assertEquals($expected, $limonetikService->getResult());
        // Retrieve the records from the Monolog TestHandler
        //test request payload from log
        $records = app('log')->getMonolog()->getHandlers()[0]->getRecords();
        $payload = $this->getPayLoad($records[0]['message']);
        $expectedUrlPart = 'PaymentOrder/Detail?Id=' . $paymentOrderId . '&AddElement=' . implode('%2C', $elements);
        $this->assertContains($expectedUrlPart, $this->getRawRq($records[0]['message']));

        $this->assertEquals([], $payload);
    }

    /**
     * @test
     */
    public function it_can_charge()
    {
        $order = $this->prepareSampleOrder(true);
        $payment = new Payment([
            'order_id' => $order->id,
            'request_id' => $this->faker->randomNumber,
            'payment_order_id' => $this->faker->numberBetween(100000000000,999999999999)
        ]);
        $payment->saveOrFail();

        $responseData = [];
        $mockResponse = new GuzzleResponse(200, ['Content-type: application/json'], json_encode($responseData));
        $limonetikService = $this->prepareService($mockResponse);
        $limonetikService->charge($payment, $order->items->all());

        $expected = $responseData;

        //test expected response
        $this->assertFalse($limonetikService->hasError());
        $this->assertEquals($expected, $limonetikService->getResult());

        // Retrieve the records from the Monolog TestHandler
        //test request payload from log
        $records = app('log')->getMonolog()->getHandlers()[0]->getRecords();
        $payload = $this->getPayLoad($records[0]['message']);
        $expectedPayload = [
            'PaymentOrderId' => $payment->payment_order_id,
            'Currency' => 'EUR',
            'ChargeAmount' => $order->items[0]->price,
            'Fees' => [['Id' => 'Commission OTS', 'Amount' => 0]]
        ];
        $this->assertEquals($expectedPayload, $payload);
        $expectedUrlPart = 'PaymentOrder/Charge';
        $this->assertContains($expectedUrlPart, $this->getRawRq($records[0]['message']));
    }

    /**
     * @test
     */
    public function it_can_pay()
    {
        $order = $this->prepareSampleOrder(true);
        $payment = new Payment([
            'order_id' => $order->id,
            'request_id' => $this->faker->randomNumber,
            'payment_order_id' => $this->faker->numberBetween(100000000000,999999999999)
        ]);
        $payment->saveOrFail();

        $merchantId = $this->faker->word;

        $responseData = [
            'PaymentOrderId' => $payment->payment_order_id,
            'RequestId' => $payment->request_id
        ];
        $mockResponse = new GuzzleResponse(200, ['Content-type: application/json'], json_encode($responseData));
        $limonetikService = $this->prepareService($mockResponse);
        $limonetikService->pay($merchantId, $order, $order->items->all());

        $expected = [];
        foreach ($responseData as $k => $v) {
            $expected[lcfirst($k)] = $v;
        }

        //test expected response
        $this->assertFalse($limonetikService->hasError());
        $this->assertEquals($expected, $limonetikService->getResult());

        // Retrieve the records from the Monolog TestHandler
        //test request payload from log
        $records = app('log')->getMonolog()->getHandlers()[0]->getRecords();
        $payload = $this->getPayLoad($records[0]['message']);
        $expectedPayload = [
            'PaymentOrder' => [
                'MerchantId' => $merchantId,
                'PaymentPageId' => 'CreditCard',
                'Amount' => $order->items[0]->price,
                'Currency' => 'EUR',
            ],
            'Credentials' => [['name' => 'technicalTransactionId', 'credential' => $payment->payment_order_id]]
        ];
        $this->assertArraySubset($expectedPayload['Credentials'], $payload['Credentials']);
        $this->assertArraySubset($expectedPayload['PaymentOrder'], $payload['PaymentOrder']);
        $expectedUrlPart = 'PaymentOrder/Pay';
        $this->assertContains($expectedUrlPart, $this->getRawRq($records[0]['message']));
    }

}