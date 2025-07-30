<?php

namespace Tests\Integration\Manipulators;

use App\Entities\UniqueProductCartEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Manipulators\CartSetter;
use App\Supplier;
use Tests\TestCase;

class CartSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;

    private function prepare()
    {
        return [
            'status_taxonomy_id' => $this->faker->randomElement(Config::get('taxonomies.cart_statuses')),
            'type' => 'individual',
            'first_name' => $this->faker->firstNameFemale,
            'last_name' => $this->faker->lastName,
            'company_name' => null,
            'site' => 'ots.local',
            'tax_number' => $this->faker->randomNumber(8),
            'country' => $this->faker->countryCode,
            'zip' => $this->faker->postCode,
            'city' => $this->faker->city,
            'address' => $this->faker->streetAddress,
            'email' => $this->faker->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'unique_products' => [
                [
                    'supplier_id' => Supplier::first()->id,
                    'name' => $this->faker->sentence,
                    'unit' => 'piece',
                    'amount' => $this->faker->randomDigitNotNull,
                    'net_price' => $this->faker->randomNumber,
                    'margin' => $this->faker->randomDigitNotNull,
                    'tax' => $this->faker->randomDigit,
                ]
            ]
        ];
    }

    /**
     * @test
     * @throws \App\Exceptions\UserException
     */
    function it_can_save_new_cart()
    {
        $cartData = $this->prepare();
        $cart1 = (new CartSetter($cartData, 'draft'))->set();
        $this->assertNotEmpty($cart1->id);
        $this->assertNotEmpty($cart1->uniqueProducts);
        $this->assertEquals($cartData['type'], $cart1->billingType->name);
        $this->assertEquals('draft', $cart1->status->name);

        $cart2 = (new CartSetter($cartData, 'sent'))->set();
        $this->assertNotEmpty($cart2->id);
        $this->assertNotEmpty($cart2->uniqueProducts);
        $this->assertEquals($cartData['type'], $cart2->billingType->name);
        $this->assertEquals('sent', $cart2->status->name);

    }

    /**
     * @test
     * @throws UserException
     * @throws \Throwable
     */
    function it_can_update_cart()
    {
        $cartData = $this->prepare();
        $cart = (new CartSetter($cartData, 'draft'))->set();
        $cartEntity = (new UniqueProductCartEntity($cart))->getFrontendData();

        $cartEntity['tax_number'] = $this->faker->randomNumber(8);
        $cartEntity['email'] = $this->faker->safeEmail;
        $cartEntity['unique_products'][0]['name'] = $this->faker->sentence;
        $cartEntity['unique_products'][0]['amount'] = $this->faker->randomDigit;

        $cartUpdated = (new CartSetter($cartEntity, 'draft'))->set();

        $this->assertEquals($cart->id, $cartUpdated->id);
        $this->assertEquals($cartEntity['tax_number'], $cartUpdated->tax_number);
        $this->assertEquals($cartEntity['email'], $cartUpdated->email);

        $entityUniqueProduct = $cartEntity['unique_products'][0];
        $uniqueProductUpdated = $cartUpdated->uniqueProducts->first();
        $this->assertEquals($entityUniqueProduct['id'], $uniqueProductUpdated->id);
        $this->assertEquals($entityUniqueProduct['name'], $uniqueProductUpdated->name);
        $this->assertEquals($entityUniqueProduct['amount'], $uniqueProductUpdated->amount);

    }

    /**
     * @test
     */
    function it_can_not_save_cart_when_unique_product_list_is_empty()
    {
        $cartData = $this->prepare();
        unset($cartData['unique_products']);
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('unique products missing');
        $cart1 = (new CartSetter($cartData, 'draft'))->set();
    }

    /**
     * @test
     */
    function it_can_not_save_cart_with_bad_save_type()
    {
        $cartData = $this->prepare();
        $this->expectException(UserException::class);
        $this->expectExceptionMessage('Bad save type!');
        $cart1 = (new CartSetter($cartData, 'fakesavetype'))->set();
    }


}
