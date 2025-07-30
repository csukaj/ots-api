<?php

namespace Tests\Integration\Entities\Payment\Limonetik;

use App\OrderItem;
use App\Services\Payment\Limonetik\Entities\ChargeEntity;
use App\Services\Payment\Limonetik\Models\Charge;
use Tests\TestCase;

class ChargeEntityTest extends TestCase
{

    /**
     * @test
     */
    public function it_can_show_frontend_data()
    {
        $chargeProperties = [
            'currency' => $this->faker->currencyCode,
            'paymentOrderId' => $this->faker->randomNumber(),
        ];
        $price =123.456;
        $margin = 10;
        $nbItems = 3;
        $expected = [
            'Currency' => $chargeProperties['currency'],
            'PaymentOrderId' => $chargeProperties['paymentOrderId'],
            'ChargeAmount' => round($nbItems * $price,2),
            'Fees' => [[
                'Id' => 'Commission OTS',
                'Amount' => round($nbItems * $margin,2)
            ]]
        ];
        foreach ($chargeProperties as $key => $val) {
            $expected[ucfirst($key)] = $val;
        }

        $orderItems = factory(OrderItem::class, $nbItems)->make(['price' => $price, 'margin' => $margin])->all();
        $actual = (new ChargeEntity(new Charge($chargeProperties)))->getFrontendData($orderItems);
        $this->assertEquals($expected, $actual);
    }

}
