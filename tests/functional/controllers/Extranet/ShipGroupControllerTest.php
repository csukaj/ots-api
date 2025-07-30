<?php

namespace Tests\Functional\Controllers\Extranet;

use App\Facades\Config;
use Tests\TestCase;

class ShipGroupControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    const TEST_DATA_DIR = __DIR__ . '/ShipGroupControllerTestData/';

    /**
     * @test
     */
    public function it_can_list_ship_groups()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/ship-group', 'GET', $token, [], true);

        $this->assertEqualsJSONFile(self::TEST_DATA_DIR . 'it_can_list_ship_groups_response.json', $responseData);
    }

    /**
     * @test
     */
    public function it_can_list_ship_groups_by_parent_id()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/ship-group?parent_id=301', 'GET', $token,
            [],
            true);

        $this->assertEqualsJSONFile(self::TEST_DATA_DIR . 'it_can_list_ship_groups_response.json', $responseData);
    }

    /**
     * @test
     */
    public function it_cant_list_ship_groups_as_nonroot()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_manager')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/ship-group', 'GET', $token, [], true);
        $this->assertEquals(['success'=> true, 'data'=>[]], $responseData);
    }

}
