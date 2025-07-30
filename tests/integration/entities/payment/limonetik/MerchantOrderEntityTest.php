<?php

namespace Tests\Integration\Entities\Payment\Limonetik;

use App\Services\Payment\Limonetik\Entities\CartItemEntity;
use App\Services\Payment\Limonetik\Entities\CustomerEntity;
use App\Services\Payment\Limonetik\Entities\MerchantOrderEntity;
use App\Services\Payment\Limonetik\Models\CartItem;
use App\Services\Payment\Limonetik\Models\Customer;
use App\Services\Payment\Limonetik\Models\MerchantOrder;
use Tests\OrderTestTrait;
use Tests\TestCase;

class MerchantOrderEntityTest extends TestCase
{
    use OrderTestTrait;

    /**
     * @test
     */
    public function it_can_show_frontend_data()
    {
        $order = $this->prepareSampleOrder();
        $orderItem = $order->items->first();
        $expected = [
            'Id' => $order->id,
            'TotalAmount' => $orderItem->price,
            'Currency' => 'EUR',
            'Customer' => (new CustomerEntity(new Customer($order), $order->locale()))->getFrontendData(),
            'CartItems' => [(new CartItemEntity(new CartItem($orderItem)))->getFrontendData()]
        ];
        $actual = (new MerchantOrderEntity(new MerchantOrder($order)))->getFrontendData();
        $this->assertEquals($expected, $actual);
    }

}
