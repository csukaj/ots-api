<?php

namespace Tests\Integration\Manipulators;

use App\Device;
use App\Manipulators\OrderItemSetter;
use App\MealPlan;
use App\Order;
use Tests\TestCase;

class OrderItemSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    function it_can_save_new_order_item()
    {
        $order = factory(Order::class)->create();
        $data = [
            'order_itemable_type' => Device::class,
            'order_itemable_id' => $this->faker->randomNumber,
            'amount' => $this->faker->randomDigit,
            'meal_plan_id' => $this->faker->randomElement(MealPlan::all()->pluck(['id'])->toArray()),
            'order_itemable_index' => $this->faker->randomDigit,
            'price' => $this->faker->randomNumber,
        ];
        //when order id not in data
        $orderItem = (new OrderItemSetter($data))->setOrderId($order->id)->set();
        $this->assertEquals($orderItem->order_id, $order->id);
        $this->assertArraySubset($data, $orderItem->attributesToArray());

        $data['order_id'] = $order->id;
        $orderItem2 = (new OrderItemSetter($data))->set();
        $this->assertArraySubset($data, $orderItem2->attributesToArray());

    }

    /**
     * @test
     */
    function it_can_update_order_item()
    {
        $order = factory(Order::class)->create();
        $data = [
            'order_id' => $order->id,
            'order_itemable_type' => Device::class,
            'order_itemable_id' => $this->faker->randomNumber,
            'amount' => $this->faker->randomDigit,
            'meal_plan_id' => $this->faker->randomElement(MealPlan::all()->pluck(['id'])->toArray()),
            'order_itemable_index' => $this->faker->randomDigit,
            'price' => $this->faker->randomNumber,
        ];
        //when order id not in data
        $orderItem = (new OrderItemSetter($data))->setOrderId($order->id)->set();
        $this->assertEquals($orderItem->order_id, $order->id);
        $this->assertArraySubset($data, $orderItem->attributesToArray());

        $data['id'] = $orderItem->id;
        $data['amount'] =  $this->faker->randomDigit;
        $orderItemUpdated = (new OrderItemSetter($data))->set();
        $this->assertEquals($orderItemUpdated->id, $orderItem->id);
        $this->assertArraySubset($data, $orderItemUpdated->attributesToArray());
    }

    /**
     * @test
     */
    function it_can_not_save_order_item_with_bad_input_data()
    {
        $this->markTestIncomplete('no validation yet in orderItemSetter');
    }


}
