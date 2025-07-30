<?php

namespace Tests\Integration\Manipulators;

use App\Cart;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Manipulators\OrderSetter;
use App\Supplier;
use App\UniqueProduct;
use Carbon\Carbon;
use Tests\TestCase;

class OrderSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare(): array
    {
        $cart = factory(Cart::class)->create([
            'status_taxonomy_id' => Config::get('taxonomies.cart_statuses.draft'),
            'site' => 'ots.local',
        ]);
        $uniqueProduct = factory(UniqueProduct::class)->create([
            'supplier_id' => $this->faker->randomElement(Supplier::all()->pluck('id')->toArray()),
            'cart_id' => $cart->id,
        ]);
        $orderData = ['first_name' => $this->faker->firstNameFemale,
            'last_name' => $this->faker->lastName,
            'tax_number' => $this->faker->randomNumber(8),
            'nationality' => $this->faker->countryCode,
            'email' => $this->faker->safeEmail,
            'telephone' => $this->faker->e164PhoneNumber,
            'site' => 'ots.local',
            'billing_type_taxonomy_id' => $this->faker->randomElement(\Illuminate\Support\Facades\Config::get('taxonomies.billing_types')),
            'order_items' => [
                [
                    'order_itemable_id' => $uniqueProduct->id,
                    'from_date' => $uniqueProduct->from_date,
                    'to_date' => $uniqueProduct->to_date,
                    'amount' => $uniqueProduct->amount,
                    # @todo @ivan - Az order_items tabla ugy lett megtervezve, hogy a meal_plan_id a resze, ezert a workaround
                    'meal_plan_id' => 1,
                    # @todo @ivan - irrelevans a uniqe product szempontjabol, igy drotozva van
                    'order_itemable_index' => 0,
                    'price' => $uniqueProduct->net_price,
                    'order_itemable_type' => UniqueProduct::class,
                    'margin' => $uniqueProduct->margin,
                    'tax' => $uniqueProduct->tax
                ]
            ]
        ];
        return [$orderData];
    }

    /**
     * @test
     */
    public function it_can_set_an_order()
    {

        list($orderData) = $this->prepare();
        $order = (new OrderSetter($orderData))->set();

        $orderItems = $orderData['order_items'];
        unset($orderData['order_items']);

        $this->assertTrue(!!$order->id);
        $this->assertArraySubset($orderData, $order->attributesToArray());
        $this->assertArraySubset($orderItems[0], $order->items[0]->attributesToArray());

    }

    /**
     * @test
     */
    public function it_can_filter_non_whitelist_attributes()
    {
        list($orderData) = $this->prepare();
        //@TODO why whitelist attributes are not enough to create a new order...
        // so now we create a now order without whitelist check and then try to update
        $order = (new OrderSetter($orderData))->set();
        $orderData['id'] = $order->id;
        $orderData['token'] = base64_encode(str_random());
        $orderData['token_created_at'] = Carbon::now();
        $order = (new OrderSetter($orderData, true))->set();

        $this->assertTrue(!!$order->id);
        $this->assertNull($order->token);
        $this->assertNull($order->token_created_at);
    }

    /**
     * @test
     */
    function it_can_modified()
    {
        list($orderData) = $this->prepare();
        $order = (new OrderSetter($orderData))->set();
        unset($orderData['order_items']); //@TODO do we need to delete items??
        $orderData['id'] = $order->id;
        $orderData['token'] = base64_encode(str_random());
        $orderData['token_created_at'] = Carbon::now();
        $orderUpdated = (new OrderSetter($orderData))->set();

        $this->assertTrue(!!$orderUpdated->id);
        $this->assertEquals($orderData['token'], $orderUpdated->token);
        $this->assertEquals($orderData['token_created_at'], $orderUpdated->token_created_at);
    }

    /**
     * @test
     */
    function it_cannot_set_order_with_bad_input_data()
    {
        $this->expectException(UserException::class);
        (new OrderSetter([
            'random_bad_data' => 999999999
        ]))->set();
    }
}
