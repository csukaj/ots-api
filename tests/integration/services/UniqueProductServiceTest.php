<?php

namespace Tests\Integration\Services;

use App\Cart;
use App\Entities\UniqueProductCartEntity;
use App\Services\OrderStatusHandlerService;
use App\Services\UniqueProduct\Service as UniqueProductService;
use App\Supplier;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

class UniqueProductServiceTest extends TestCase
{

    private function prepare()
    {
        return factory(Cart::class, 10)->create([
            'site' => 'ots.local'
        ]);
    }

    /**
     * @test
     */
    public function it_can_list()
    {
        $cart = $this->prepare();
        $expected = UniqueProductCartEntity::getCollection($cart->sortByDesc('id'));
        $actual = (new UniqueProductService(new Cart()))->list()->getResult();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_show()
    {
        $cart = $this->prepare()->first();
        $expected = (new UniqueProductCartEntity($cart))->getFrontendData();
        $actual = (new UniqueProductService(new Cart()))->show($cart->id)->getResult();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_can_save_draft_cart()
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
        $service = (new UniqueProductService(new Cart()))->save($cartData, 'draft');
        $this->assertFalse($service->hasError());
        $actual = Cart::orderBy('id', 'desc')->first()->attributesToArray();

        unset($actual['id'], $cartData['unique_products'], $cartData['type']);
        $this->assertArraySubset($cartData, $actual);


        //update
        $cart = $carts[1];
        $cart->status_taxonomy_id = Config::get('taxonomies.cart_statuses.draft');
        $cart->saveOrFail();
        $cartData = (new UniqueProductCartEntity($cart))->getFrontendData();
        $cartData['last_name'] = $this->faker->lastName;
        $cartData['email'] = $this->faker->safeEmail;

        $service = (new UniqueProductService(new Cart()))->save($cartData, 'draft');
        $this->assertFalse($service->hasError());
        $actual = Cart::findOrFail($cartData['id']);
        $this->assertEquals($cartData['id'], $actual->id);
        $this->assertEquals($cartData['last_name'], $actual->last_name);
        $this->assertEquals($cartData['email'], $actual->email);
    }

    /**
     * @test
     */
    public function it_can_save_sent_cart()
    {
        $carts = $this->prepare();

        $cart = $carts[0];
        $cartData = $cart->attributesToArray();
        $cartData['status_taxonomy_id'] = Config::get('taxonomies.cart_statuses.sent');
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
        $service = (new UniqueProductService(new Cart()))->save($cartData, 'sent');
        $this->assertFalse($service->hasError());
        $actual = Cart::orderBy('id', 'desc')->first()->attributesToArray();

        unset($actual['id'], $cartData['unique_products'], $cartData['type']);
        $this->assertArraySubset($cartData, $actual);

        //update
        $cart = $carts[1];
        $cartData = (new UniqueProductCartEntity($cart))->getFrontendData();
        $cartData['last_name'] = $this->faker->lastName;
        $cartData['email'] = $this->faker->safeEmail;

        $service = (new UniqueProductService(new Cart()))->save($cartData, 'sent');
        $this->assertFalse($service->hasError());
        $actual = Cart::findOrFail($cartData['id']);
        $this->assertEquals($cartData['id'], $actual->id);
        $this->assertEquals($cartData['last_name'], $actual->last_name);
        $this->assertEquals($cartData['email'], $actual->email);
    }


    /**
     * @test
     */
    public function it_can_delete()
    {
        $carts = $this->prepare();
        $cart1 = $carts[0];
        $cart1->status_taxonomy_id = Config::get('taxonomies.cart_statuses.draft');
        $cart1->saveOrFail();
        $cart2 = $carts[1];
        $cart2->status_taxonomy_id = Config::get('taxonomies.cart_statuses.sent');
        $cart2->saveOrFail();


        //delete draft
        $service = (new UniqueProductService(new Cart()))->delete($cart1->id);
        $this->assertNotEmpty(Cart::onlyTrashed()->find($cart1->id));
        $this->assertEmpty($service->getResult());
        $this->assertFalse($service->hasError());
        $this->assertEmpty($service->getErrorMessages());


        //try to delete sent
        $service = (new UniqueProductService(new Cart()))->delete($cart2->id);
        $this->assertEmpty($service->getResult());
        $this->assertTrue($service->hasError());
        $this->assertEquals(['This cart not in draft status, you cannot delete it!'], $service->getErrorMessages());
        $this->assertNotEmpty(Cart::find($cart2->id));
    }

}