<?php

namespace Tests\Functional\Controllers\Admin;

use App\Cart;
use App\Entities\UniqueProductCartEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Supplier;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ALWAYS;

    private function prepare()
    {
        return factory(Cart::class, 10)->create([
            'site' => 'ots.local',
            'status_taxonomy_id' => Config::get('taxonomies.cart_statuses.draft')
        ]);
    }

    /**
     * @test
     */
    public function it_can_index()
    {
        $cart = $this->prepare();
        $expected = UniqueProductCartEntity::getCollection($cart->sortByDesc('id'));
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/cart", 'GET', $token, [], true);
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_show()
    {
        $cart = $this->prepare()->first();
        $expected = (new UniqueProductCartEntity($cart))->getFrontendData();
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/cart/" . $cart->id, 'GET', $token, [], true);
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_store()
    {
        $carts = $this->prepare();

        //new
        $cart = $carts[0];
        $cartData = $cart->attributesToArray();
        $cartData['status_taxonomy_id'] = Config::get('taxonomies.cart_statuses.draft');
        $cartData['type'] = $cart->billingType->name;
        $cartData['unique_products'] = [
            [
                'supplier_id' => Supplier::first()->id,
                'name' => $this->faker->sentence,
                'unit' => 'piece',
                'amount' => $this->faker->randomDigitNotNull,
                'net_price' => $this->faker->randomNumber,
                'margin' => $this->faker->randomDigitNotNull,
                'tax' => $this->faker->randomDigit,

            ]
        ];
        unset($cartData['id']);

        //save as draft
        $data = ['data'=>$cartData, 'saveType'=>'draft'];

        $countBefore = Cart::count();
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/cart", 'POST', $token, $data, true);
        $this->assertEquals([], $responseData['data']);
        $this->assertEquals($countBefore+1, Cart::count());

        //save as sent
        $data = ['data'=>$cartData, 'saveType'=>'sent'];

        $countBefore = Cart::count();
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/cart", 'POST', $token, $data, true);
        $this->assertEquals([], $responseData['data']);
        $this->assertEquals($countBefore+1, Cart::count());


    }

    /**
     * @test
     */
    public function it_can_destroy()
    {
        $cart = $this->prepare()->first();
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $this->assertSuccessfulHttpApiRequest("/admin/cart/" . $cart->id, 'DELETE', $token, [], true);

        $this->assertNotEmpty(Cart::onlyTrashed()->find($cart->id));
    }

    /**
     * @test
     */
    public function it_can_not_destroy_a_sent_cart()
    {
        $cart = $this->prepare()->first();
        $cart->status_taxonomy_id = Config::get('taxonomies.cart_statuses.sent');
        $cart->saveOrFail();

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list (,$responseData,$response) = $this->httpApiRequest("/admin/cart/" . $cart->id, 'DELETE', $token, [], true);

        $response->assertStatus(403);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('This cart not in draft status, you cannot delete it!',$responseData['error']);


    }
}