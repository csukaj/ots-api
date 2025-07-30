<?php

namespace Tests\Functional\Controllers\Admin;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\Program;
use Tests\TestCase;

class ProgramControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    const TEST_DATA_DIR = __DIR__ . '/ProgramControllerTestData/';

    /**
     * @test
     */
    public function it_can_list_programs()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/program', 'GET', $token, [], true);

        $expectedResponseBody = json_decode(
            file_get_contents(self::TEST_DATA_DIR . 'it_can_list_programs_response.json'),
            true
        );
        $this->assertEquals($expectedResponseBody, $responseData);
    }

    /**
     * @test
     */
    public function it_can_get_a_program_by_id()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/program/1', 'GET', $token, [], true);

        $expectedResponseBody = json_decode(
            file_get_contents(self::TEST_DATA_DIR . 'it_can_get_a_program_by_id_response.json'),
            true
        );
        $this->assertEquals($expectedResponseBody, $responseData);
    }

    /**
     * @test
     */
    public function it_can_create_a_program()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/program', 'POST', $token, [
            'name' => [
                'en' => 'Test program'
            ],
            'descriptions' => [
                'long_description' => [
                    'en' => 'Test program description'
                ]
            ],
            'type' => 'activity',
            'organization_id' => 1
        ], true);

        $this->assertEquals('Test program', $responseData['data']['name']['en']);
        $this->assertEquals('Test program description',
            $responseData['data']['descriptions']['long_description']['en']);
    }

    /**
     * @test
     */
    public function it_can_update_a_program()
    {
        $program = Program::findOrFail(1);
        $this->assertEquals('Itinerary A', $program->name->description);
        $this->assertEquals(
            "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus auctor pretium lobortis. Sed interdum tempor arcu. Pellentesque pretium hendrerit fringilla. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse ultricies turpis a aliquet tempor. Suspendisse aliquam bibendum egestas. Duis sollicitudin justo erat, vel dictum libero placerat sed. Nam et mattis tellus. Vestibulum vitae enim vel velit iaculis fermentum.\nNulla sit amet tempor ex. Morbi urna eros, ultrices in tortor vitae, faucibus tincidunt leo. Aenean consectetur felis sit amet cursus facilisis. Curabitur et diam finibus metus egestas tincidunt. Nullam porttitor tincidunt elementum. Quisque elementum rhoncus aliquam. Suspendisse pretium scelerisque luctus. Proin mollis condimentum nisl a fermentum.\nSuspendisse fermentum nisi nec egestas elementum. Nullam pulvinar accumsan dui, sit amet pretium tellus consectetur id. Duis purus dolor, euismod ut nisi et, gravida euismod massa. Aenean vehicula volutpat faucibus. Sed risus dui, porttitor non tristique ac, feugiat nec erat. Aenean non dui ex. Pellentesque tincidunt commodo felis, ac fringilla arcu auctor hendrerit. Etiam volutpat massa et erat sollicitudin accumsan. Nam mattis sollicitudin risus a pharetra. Suspendisse et imperdiet tortor. Aliquam odio neque, accumsan quis convallis et, lacinia nec leo. Sed facilisis volutpat tellus, ut mattis diam ullamcorper quis. Etiam id cursus eros.",
            $program->descriptions[0]->description->description
        );

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/program/1', 'PUT', $token, [
            'name' => [
                'en' => 'Modified name'
            ],
            'descriptions' => [
                'long_description' => [
                    'en' => 'Modified description'
                ]
            ],
            'type' => 'activity',
            'organization_id' => 1
        ], true);

        $this->assertEquals('Modified name', $responseData['data']['name']['en']);
        $this->assertEquals('Modified description', $responseData['data']['descriptions']['long_description']['en']);

        $program = Program::findOrFail(1);
        $this->assertEquals('Modified name', $program->name->description);
        $this->assertEquals(
            'Modified description',
            $program->descriptions[0]->description->description
        );
    }

    /**
     * @test
     */
    public function it_can_delete_a_program()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $program = new Program();
        $program->type_taxonomy_id = Config::getOrFail('taxonomies.program_types.activity');
        $program->organization_id = 1;
        $program->name_description_id = 1;
        $program->location_id = 1;
        $program->saveOrFail();

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/program/' . $program->id, 'DELETE', $token, [],
            true);

        $this->assertNull(Program::find($program->id));
    }

    /**
     * @test
     */
    public function it_can_not_delete_a_program_with_existing_relations()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(,$responseData, $response) = $this->httpApiRequest('/admin/program/1', 'DELETE', $token, [], true);

        $response->assertStatus(400);
        $this->assertFalse($responseData['success']);
        $this->assertEquals("An activity with active relation can not be deleted!",$responseData['error']);

        $this->assertNotNull(Program::find(1));
    }

}
