<?php

namespace Tests\Integration\Entities\Payment\Limonetik;

use App\Services\Payment\Limonetik\Entities\MerchantOrderEntity;
use App\Services\Payment\Limonetik\Entities\PaymentOrderEntity;
use App\Services\Payment\Limonetik\Models\MerchantOrder;
use App\Services\Payment\Limonetik\Models\PaymentOrder;
use Tests\OrderTestTrait;
use Tests\TestCase;

class PaymentOrderEntityTest extends TestCase
{
    use OrderTestTrait;

    /**
     * @test
     */
    public function it_can_show_frontend_data()
    {
        $order = $this->prepareSampleOrder();
        $paymentOrder = new PaymentOrder($order);
        $paymentOrder
            ->setMerchantId($this->faker->word)
            ->setPaymentPageId($this->faker->word)
            ->setAmountByOrderItems($order->items->all())
            ->setCurrency($this->faker->currencycode)
            ->setMerchantUrls([$this->faker->url]);
        $expected = [
            'MerchantId' => $paymentOrder->merchantId,
            'PaymentPageId' => $paymentOrder->paymentPageId,
            'Amount' => $paymentOrder->amount,
            'Currency' => $paymentOrder->currency,
            'MerchantUrls' => $paymentOrder->merchantUrls,
            'MerchantOrder' => (new MerchantOrderEntity(new MerchantOrder($order)))->getFrontendData()
        ];
        $actual = (new PaymentOrderEntity($paymentOrder))->getFrontendData();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_show_frontend_data_by_order_items()
    {
        $order = $this->prepareSampleOrder();
        $paymentOrder = new PaymentOrder($order);
        $paymentOrder
            ->setMerchantId($this->faker->word)
            ->setPaymentPageId($this->faker->word)
            ->setAmountByOrderItems($order->items->all())
            ->setCurrency($this->faker->currencycode)
            ->setMerchantUrls([$this->faker->url]);
        $expected = [
            'MerchantId' => $paymentOrder->merchantId,
            'PaymentPageId' => $paymentOrder->paymentPageId,
            'Amount' => $paymentOrder->amount,
            'Currency' => $paymentOrder->currency,
            'MerchantUrls' => $paymentOrder->merchantUrls,
            'MerchantOrder' => (new MerchantOrderEntity(new MerchantOrder($order)))->getFrontendData($order->items->all())
        ];
        $actual = (new PaymentOrderEntity($paymentOrder))->getFrontendData();
        $this->assertEquals($expected, $actual);
    }

}
