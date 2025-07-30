<?php

namespace Tests\Integration\Entities\Payment\Limonetik;

use App\Services\Payment\Limonetik\Entities\CancelEntity;
use App\Services\Payment\Limonetik\Models\Cancel;
use Tests\TestCase;

class CancelEntityTest extends TestCase
{

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_show_frontend_data()
    {
        $cancelProperties = [
            'cancelAmount' => $this->faker->randomNumber(),
            'currency' => $this->faker->currencyCode,
            'paymentOrderId' => $this->faker->randomNumber(),
            'merchantOrderId' => $this->faker->randomNumber(),
            'merchantId' => $this->faker->randomNumber(),
            'merchantOperationId' => $this->faker->randomNumber(),
        ];
        $expected = [];
        foreach ($cancelProperties as $key => $val) {
            $expected[ucfirst($key)] = $val;
        }
        $actual = (new CancelEntity(new Cancel($cancelProperties)))->getFrontendData();
        $this->assertEquals($expected, $actual);
    }

}
