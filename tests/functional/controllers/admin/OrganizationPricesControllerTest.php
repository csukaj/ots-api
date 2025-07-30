<?php

namespace Tests\Functional\Controllers\Admin;

use App\Entities\OrganizationEntity;
use App\Organization;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OrganizationPricesControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    /**
     * @test
     * @group controller-write
     * @throws \Exception
     */
    public function it_can_show_an_organization_with_prices()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/organization-prices/1', 'GET', $token,
            ['product-type' => 'accommodation'], true);

        $this->assertEquals(
            (new OrganizationEntity(Organization::find(1), null, null, 'accommodation'))
                ->getFrontendData(['date_ranges', 'devices', 'prices', 'device_margin', 'pricing', 'device_amount']),
            $responseData['data']
        );
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_update_an_organization()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/organization-prices/1', 'GET', $token, [], true);

        $this->assertEquals('from_net_price', $responseData['data']['pricing_logic']);
        $this->assertEquals('percentage', $responseData['data']['margin_type']);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/organization-prices/1', 'PUT', $token, [
            'pricing_logic' => 'from_rack_price',
            'margin_type' => 'value'
        ], true);

        $this->assertEquals('from_rack_price', $responseData['data']['pricing_logic']);
        $this->assertEquals('value', $responseData['data']['margin_type']);
    }
}
