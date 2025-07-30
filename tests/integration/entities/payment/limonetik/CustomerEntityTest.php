<?php

namespace Tests\Integration\Entities\Payment\Limonetik;

use App\Order;
use App\Services\Payment\Limonetik\Entities\CustomerEntity;
use App\Services\Payment\Limonetik\Models\Customer;
use Tests\TestCase;

class CustomerEntityTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_show_frontend_data()
    {
        $order = factory(Order::class)->create();
        $locale = 'hu_HU';
        $expected = [
            'Id' => $order->id,
            'Email' => $order->email,
            'FirstName' => $order->first_name,
            'LastName' => $order->last_name,
            'Culture' => $locale
        ];
        $actual = (new CustomerEntity(new Customer($order), $locale))->getFrontendData();
        $this->assertEquals($expected, $actual);
    }

}
