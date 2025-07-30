<?php

namespace Tests\Functional\Controllers;

use App\Services\Billing\Service as BillingService;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\TestCase;

class BillingControllerTest extends TestCase
{
    const ORDER_ID = 'order_id';
    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     * @  runInSepara   teProcess
     *
     * 'runInSeparateProcess' needed because of session_start in invoiceagent
     */
    public function it_can_create()
    {
        $mockedService = Mockery::mock(BillingService::class);
        $mockedService->shouldReceive('hasError')->once()->andReturn(false);
        $mockedService->shouldReceive('create')->once()->andReturnSelf();
        App::instance('billing', $mockedService);

        $body = [self::ORDER_ID => $this->faker->randomNumber];
        $responseData = $this->assertSuccessfulHttpApiRequest("/billing/create", 'POST', [], $body);
        $this->assertEquals((object)[
            'success' => true,
            'data' => []
        ], $responseData);

    }

    /**
     * @test
     * @  runInSepara   teProcess
     *
     * 'runInSeparateProcess' needed because of session_start in invoiceagent
     */
    public function it_cant_create_with_bad_order_info()
    {
        $body = [self::ORDER_ID => $this->faker->randomNumber];

        $mockedService = Mockery::mock(BillingService::class);
        $mockedService->shouldReceive('hasError')->once()->andReturn(true);
        $mockedService->shouldReceive('getErrorMessages')->once()->andReturn(['Order with "' . $body[self::ORDER_ID] . '" has no payment information']);
        $mockedService->shouldReceive('create')->once()->andReturnSelf();
        App::instance('billing', $mockedService);

        list(, , $response) = $this->httpApiRequest("/billing/create", 'POST', [], $body);
        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => false,
                'data' => ['Order with "' . $body[self::ORDER_ID] . '" has no payment information']
            ]);

    }

}