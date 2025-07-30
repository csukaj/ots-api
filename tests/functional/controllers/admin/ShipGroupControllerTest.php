<?php

namespace Tests\Functional\Controllers\Admin;

use App\Facades\Config;
use App\ShipGroup;
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
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/ship-group', 'GET', $token, [], true);

        $this->assertEqualsJSONFile(self::TEST_DATA_DIR . 'it_can_list_ship_groups_response.json', $responseData);
    }

    /**
     * @test
     */
    public function it_can_list_ship_groups_by_parent_id()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/ship-group?parent_id=301', 'GET', $token, [],
            true);

        $this->assertEqualsJSONFile(self::TEST_DATA_DIR . 'it_can_list_ship_groups_response.json', $responseData);
    }

    /**
     * @test
     */
    public function it_can_get_a_ship_group_by_id()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/ship-group/1', 'GET', $token, [], true);

        $this->assertEqualsJSONFile(self::TEST_DATA_DIR . 'it_can_get_a_ship_group_by_id_response.json', $responseData);
    }

    /**
     * @test
     */
    public function it_can_create_a_ship_group()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/admin/ship-group',
            'POST',
            $token,
            [
                'name' => [
                    'en' => 'Test ship group'
                ],
                'descriptions' => [
                    'long_description' => [
                        'en' => 'Test ship group description'
                    ]
                ],
                'properties' => [
                    'Ship Group Category' => [
                        'name' => 'Ship Group Category',
                        'value' => 'Monohull',
                        'categoryId' => 379
                    ],
                    'Propulsion' => [
                        'name' => 'Propulsion',
                        'value' => 'Motorboat',
                        'categoryId' => 379
                    ],
                    'Discount calculations base' => [
                        'name' => 'Discount calculations base',
                        'value' => 'rack prices',
                        'categoryId' => 383
                    ],
                    'Merged free nights' => [
                        'name' => 'Merged free nights',
                        'value' => 'enabled',
                        'categoryId' => 383
                    ]
                ]
            ],
            true
        );

        $this->assertEquals('Test ship group', $responseData['data']['name']['en']);
        $this->assertEquals(
            'Test ship group description', $responseData['data']['descriptions']['long_description']['en']
        );
    }

    /**
     * @test
     */
    public function it_can_delete_a_ship_group()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $this->assertSuccessfulHttpApiRequest('/admin/ship-group/1', 'DELETE', $token, [], true);

        $this->assertNotEmpty(ShipGroup::onlyTrashed()->find(1));
    }
}
