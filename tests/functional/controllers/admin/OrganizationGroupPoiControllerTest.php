<?php

namespace Tests\Functional\Controllers\Admin;

use App\Facades\Config;
use App\OrganizationGroupPoi;
use Tests\TestCase;

class OrganizationGroupPoiControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    const TEST_DATA_DIR = __DIR__ . '/OrganizationGroupPoiControllerTestData/';

    /**
     * @test
     */
    public function it_can_list_org_grp_pois()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/organization-group-poi', 'GET', $token, [],
            true);

        $expectedResponseBody = json_decode(file_get_contents(self::TEST_DATA_DIR . 'it_can_list_org_grp_pois_response.json'),
            true);
        $this->assertEquals($expectedResponseBody, $responseData);
    }

    /**
     * @test
     */
    public function it_can_get_an_org_grp_poi_by_id()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/organization-group-poi/1', 'GET', $token, [],
            true);

        $expectedResponseBody = json_decode(file_get_contents(self::TEST_DATA_DIR . 'it_can_get_an_org_grp_poi_by_id_response.json'),
            true);
        $this->assertEquals($expectedResponseBody, $responseData);
    }

    /**
     * @test
     */
    public function it_can_create_a_org_grp_poi()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/organization-group-poi', 'POST', $token, [
            'type' => 'Home Port',
            'organization_group_id' => 1,
            'poi' => [
                'type' => 'Port',
                'name' => [
                    'en' => 'Test organization group POI'
                ],
                'description' => [
                    'en' => 'Test organization group POI description'
                ],
                'location' => [
                    'island' => 'Mahé',
                    'district' => 'Bel Air',
                    'latitude' => '-4.622432',
                    'longitude' => '55.4588122'
                ]
            ]
        ], true);

        $this->assertEquals('Test organization group POI', $responseData['data']['poi']['name']['en']);
        $this->assertEquals('Test organization group POI description', $responseData['data']['poi']['description']['en']);
    }

    /**
     * @test
     */
    public function it_can_update_a_org_grp_poi()
    {
        $orgGrpPoi = OrganizationGroupPoi::findOrFail(1);
        $this->assertEquals('Port of Victoria', $orgGrpPoi->poi->name->description);
        $this->assertEquals(
            'Although it is the capital of the Seychelles, Victoria on Mahe island is not a large city. It was originally settled in 1778 by the French but was eventually named after Queen Victoria. There is an old part of town with narrow streets and dilapidated colonial buildings, and a new part of the city with wider avenues and tropical gardens. The centre of the city is pinpointed by the clock tower, a copy of the Little Ben outside Victoria station in London, next to which stands the court house. The Anglican and Roman Catholic cathedrals are just a couple of blocks away from one another, but they are both outdone by the impressive Capuchin House, a seminary for priests. A more lively pleasure is to be found at the morning market where the stalls are stacked with tropical fruits, spices and freshly caught fish.',
            $orgGrpPoi->poi->description->description
        );

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/organization-group-poi/1', 'PUT', $token, [
            'type' => 'Home Port',
            'organization_group_id' => 1,
            'poi' => [
                'type' => 'Port',
                'name' => [
                    'en' => 'Modified name'
                ],
                'description' => [
                    'en' => 'Modified description'
                ],
                'location' => [
                    'island' => 'Mahé',
                    'district' => 'Bel Air',
                    'latitude' => '-4.622432',
                    'longitude' => '55.4588122'
                ]
            ]
        ], true);

        $this->assertEquals('Modified name', $responseData['data']['poi']['name']['en']);
        $this->assertEquals('Modified description', $responseData['data']['poi']['description']['en']);

        $orgGrpPoi = OrganizationGroupPoi::findOrFail(1);
        $this->assertEquals('Modified name', $orgGrpPoi->poi->name->description);
        $this->assertEquals('Modified description', $orgGrpPoi->poi->description->description);
    }

    /**
     * @test
     */
    public function it_can_delete_a_org_grp_poi()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $this->assertSuccessfulHttpApiRequest('/admin/organization-group-poi/1', 'DELETE', $token, [],true);

        $this->assertNull(OrganizationGroupPoi::find(1));
    }

}
