<?php

namespace Tests\Functional\Controllers\Admin;

use App\Facades\Config;
use App\Cruise;
use Tests\TestCase;

class CruiseControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    const TEST_DATA_DIR = __DIR__ . '/CruiseControllerTestData/';

    /**
     * @test
     */
    public function it_can_list_cruises()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/cruise', 'GET', $token, [], true);

        $expectedResponseBody = json_decode(file_get_contents(self::TEST_DATA_DIR . 'it_can_list_cruises_response.json'),
            true);
        $this->assertEquals($expectedResponseBody, $responseData);
    }

    /**
     * @test
     */
    public function it_can_get_a_cruise_by_id()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/cruise/1', 'GET', $token, [], true);

        $expectedResponseBody = json_decode(
            file_get_contents(self::TEST_DATA_DIR . 'it_can_get_a_cruise_by_id_response.json'),
            true
        );
        $this->assertEquals($expectedResponseBody, $responseData);
    }

    /**
     * @test
     */
    public function it_can_create_a_cruise()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/cruise', 'POST', $token, [
            'name' => [
                'en' => 'Test cruise'
            ],
            'descriptions' => [
                'long_description' => [
                    'en' => 'Test cruise description'
                ]
            ],
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 410],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 410]
            ]
        ], true);

        $this->assertEquals('Test cruise', $responseData['data']['name']['en']);
        $this->assertEquals('Test cruise description', $responseData['data']['descriptions']['long_description']['en']);
    }

    /**
     * @test
     */
    public function it_can_update_a_cruise()
    {
        $cruise = Cruise::findOrFail(1);
        $this->assertEquals('Cruise A', $cruise->name->description);
        $this->assertEquals(
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus auctor pretium lobortis. Sed interdum tempor arcu. Pellentesque pretium hendrerit fringilla. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse ultricies turpis a aliquet tempor. Suspendisse aliquam bibendum egestas. Duis sollicitudin justo erat, vel dictum libero placerat sed. Nam et mattis tellus. Vestibulum vitae enim vel velit iaculis fermentum.\nNulla sit amet tempor ex. Morbi urna eros, ultrices in tortor vitae, faucibus tincidunt leo. Aenean consectetur felis sit amet cursus facilisis. Curabitur et diam finibus metus egestas tincidunt. Nullam porttitor tincidunt elementum. Quisque elementum rhoncus aliquam. Suspendisse pretium scelerisque luctus. Proin mollis condimentum nisl a fermentum.\nSuspendisse fermentum nisi nec egestas elementum. Nullam pulvinar accumsan dui, sit amet pretium tellus consectetur id. Duis purus dolor, euismod ut nisi et, gravida euismod massa. Aenean vehicula volutpat faucibus. Sed risus dui, porttitor non tristique ac, feugiat nec erat. Aenean non dui ex. Pellentesque tincidunt commodo felis, ac fringilla arcu auctor hendrerit. Etiam volutpat massa et erat sollicitudin accumsan. Nam mattis sollicitudin risus a pharetra. Suspendisse et imperdiet tortor. Aliquam odio neque, accumsan quis convallis et, lacinia nec leo. Sed facilisis volutpat tellus, ut mattis diam ullamcorper quis. Etiam id cursus eros.",
            $cruise->descriptions[0]->description->description
        );

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/cruise/1', 'PUT', $token, [
            'name' => [
                'en' => 'Modified name'
            ],
            'descriptions' => [
                'long_description' => [
                    'en' => 'Modified description'
                ]
            ]
        ], true);

        $this->assertEquals('Modified name', $responseData['data']['name']['en']);
        $this->assertEquals('Modified description', $responseData['data']['descriptions']['long_description']['en']);

        $cruise = Cruise::findOrFail(1);
        $this->assertEquals('Modified name', $cruise->name->description);
        $this->assertEquals(
            'Modified description',
            $cruise->descriptions[0]->description->description
        );
    }

    /**
     * @test
     */
    public function it_can_delete_a_cruise()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $this->assertSuccessfulHttpApiRequest('/admin/cruise/1', 'DELETE', $token, [], true);

        $this->assertNull(Cruise::find(1));
    }

}
