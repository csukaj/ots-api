<?php

namespace Tests\Integration\Entities;

use App\AgeRange;
use App\Entities\OrderEntity;
use App\Entities\OrderItemEntity;
use App\Order;
use App\OrderItem;
use App\OrderItemGuest;
use App\Organization;
use Tests\TestCase;

class OrderEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function createOrder(): Order
    {
        return factory(Order::class,1)->create()->each(function ($order) {
            $item = factory(OrderItem::class)->create([
                'order_id' => $order->id
            ]);
            $ageRange = factory(AgeRange::class)->create([
                'age_rangeable_type' => Organization::class,
                'age_rangeable_id' => $item->orderItemable->deviceable->id,
            ]);
            factory(OrderItemGuest::class)->create([
                'order_item_id' => $item->id,
                'age_range_id' => $ageRange->id,
            ]);

        })->first();
    }


    /**
     * @test
     */
    function an_order_has_frontend_data()
    {

        $order =  $this->createOrder();
        $frontendData = (new OrderEntity($order))->getFrontendData();

        $orderData = $order->attributesToArray();
        $orderData['created_at'] = $order->created_at->toIso8601ZuluString();
        $orderData['updated_at'] = $order->updated_at->toIso8601ZuluString();
        $orderData['status'] = ($order->statusTx) ? $order->statusTx->name : null;
        $orderData['items'] = OrderItemEntity::getCollection($order->items);
        $orderData['billing_type'] = $order->billingType->name;

        $this->assertEquals($orderData, $frontendData);
    }

}
