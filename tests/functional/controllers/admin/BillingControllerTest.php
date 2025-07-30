<?php

namespace Tests\Functional\Controllers\Admin;

use App\Services\Billing\Service as BillingService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Mockery;
use Tests\TestCase;

class BillingControllerTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    public function it_can_billinggetpdf()
    {

        $fakePdf = '%PDF-1.4' . implode('', array_map(function ($i) {
                return chr(rand(0, 255));
            }, range(0, 5)));

        $mockedService = Mockery::mock(BillingService::class);
        $mockedService->shouldReceive('getPdf')->once()->andReturnSelf();
        $mockedService->shouldReceive('hasError')->once()->andReturn(false);
        $mockedService->shouldReceive('getPdfInvoice')->once()->andReturn($fakePdf);

        App::instance('billing', $mockedService);

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $id = $this->faker->randomNumber;
        list(,,$response) = $this->httpApiRequest('/admin/billing/getpdf/' . $id, 'GET', $token);
        $response->assertStatus(200);
        $this->assertEquals($fakePdf,$response->getContent());
    }

    /**
     * @test
     */
    public function it_cant_billinggetpdf_with_bad_id()
    {

        $mockedService = Mockery::mock(BillingService::class);
        $mockedService->shouldReceive('getPdf')->once()->andReturnSelf();
        $mockedService->shouldReceive('hasError')->once()->andReturn(true);

        App::instance('billing', $mockedService);

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $id = $this->faker->randomNumber;
        list(, , $response) = $this->httpApiRequest('/admin/billing/getpdf/' . $id, 'GET', $token);
        $response
            ->assertStatus(400)
            ->assertJson(["success" => false, "error" => "Error downloading pdf"]);
    }
}