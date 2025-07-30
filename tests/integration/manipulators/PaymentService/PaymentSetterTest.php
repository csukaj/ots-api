<?php

namespace Tests\Integration\Manipulators\PaymentService;

use App\Exceptions\UserException;
use App\Order;
use App\Services\Payment\Manipulators\PaymentSetter;
use Tests\TestCase;

class PaymentSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     * @throws UserException
     */
    function it_can_save_new_payment()
    {
        $order = factory(Order::class)->create();
        $data = [
            'payment_order_id' => $this->faker->numberBetween(100000000000,999999999999),
            'supplier_id' => $this->faker->randomNumber,
            'request_id' => $this->faker->word,
            'order_id' => $order->id,
        ];
        $payment = (new PaymentSetter($data))->set();

        $this->assertNotEmpty($payment->id);
        $this->assertArraySubset($data, $payment->attributesToArray());
        $this->assertEquals(json_encode([date('Y-m-d H:i:s') => ['status' => 'Created']]), $payment->status_log);
    }

    /**
     * @test
     * @throws UserException
     */
    function it_can_update_payment()
    {
        $order = factory(Order::class)->create();
        $data = [
            'payment_order_id' => $this->faker->numberBetween(100000000000,999999999999),
            'supplier_id' => $this->faker->randomNumber,
            'request_id' => $this->faker->word,
            'order_id' => $order->id,
        ];
        $payment = (new PaymentSetter($data))->set();
        $this->assertNotEmpty($payment->id);

        $dataUpdate = ['id' => $payment->id, 'status' => 'Other status'];

        $paymentUpd = (new PaymentSetter($dataUpdate))->set();
        $this->assertEquals($payment->id, $paymentUpd->id);
        $expectedStatusLog = json_encode([
            date('Y-m-d H:i:s') => ['status' => 'Created'],
            date('Y-m-d H:i:s') => ['status' => $dataUpdate['status']]
        ]);
        $this->assertEquals($expectedStatusLog, $paymentUpd->status_log);
        //setter can only update status
    }

    /**
     * @test
     * @throws UserException
     */
    function it_can_not_set_payment_with_bad_input_data()
    {
        $this->expectException(UserException::class);
        $order = factory(Order::class)->create();
        $data = [
            'payment_order_id' => $this->faker->numberBetween(100000000000,999999999999),
            'supplier_id' => $this->faker->word, //this is !is_string
            'request_id' => $this->faker->word,
            'order_id' => $order->id,
        ];
        $payment = (new PaymentSetter($data))->set();
        $this->assertNotEmpty($payment->id);

    }


}
