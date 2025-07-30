<?php

namespace Tests\Integration\Entities\Payment\Limonetik;

use App\Services\Payment\Limonetik\Entities\CartItemEntity;
use App\Services\Payment\Limonetik\Models\CartItem;
use Tests\OrderTestTrait;
use Tests\TestCase;

class CartItemEntityTest extends TestCase
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
            'Id' => $orderItem->id,
            'UnitPrice' => $orderItem->price,
            'Quantity' => $orderItem->amount
        ];
        $actual = (new CartItemEntity(new CartItem($orderItem)))->getFrontendData();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_get_collection()
    {
        $order = $this->prepareSampleOrder();
        $orderItem = $order->items->first();
        $expected = [
            'Id' => $orderItem->id,
            'UnitPrice' => $orderItem->price,
            'Quantity' => $orderItem->amount
        ];
        $items = [];
        foreach ($order->items as $orderItem) {
            $items[] = new CartItem($orderItem);
        }
        $actual = CartItemEntity::getCollection($items);
        $this->assertEquals([$expected], $actual);
    }

}
