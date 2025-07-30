<?php

namespace Tests\Functional\Controllers\Admin;

use App\Entities\ProgramRelationEntity;
use App\Facades\Config;
use App\Location;
use App\Program;
use App\ProgramRelation;
use Tests\TestCase;

class ProgramRelationControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    const TEST_DATA_DIR = __DIR__ . '/ProgramRelationControllerTestData/';

    /**
     * @test
     */
    public function it_can_list_all_program_relations()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , $response) = $this->httpApiRequest('/admin/program-relation', 'GET', $token, [], true);
        
        $response->assertStatus(200);
        $this->assertEqualsJSONFile(self::TEST_DATA_DIR . 'it_can_list_all_program_relations_response.json', $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_list_program_relations_of_a_parent()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , $response) = $this->httpApiRequest('/admin/program-relation?parent_id=1', 'GET', $token, [],
            true);

        $response->assertStatus(200);
        $this->assertEqualsJSONFile(self::TEST_DATA_DIR . 'it_can_list_all_program_relations_response.json', $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_get_a_program_relation_by_id()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , $response) = $this->httpApiRequest('/admin/program-relation/1', 'GET', $token, [], true);

        $response->assertStatus(200);
        $this->assertEqualsJSONFile(self::TEST_DATA_DIR . 'it_can_get_a_program_relation_by_id_response.json', $response->getContent());
    }

    /**
     * @test
     */
    public function it_can_create_a_program_relation()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, $responseData, $response) = $this->httpApiRequest('/admin/program-relation', 'POST', $token, [
            'parent_id' => 2,
            'child_id' => 1,
            'sequence' => 1,
            'relative_time' => [
                'day' => 22,
                'precision' => 'time_of_day',
                'time_of_day' => 'AM',
                'time' => null
            ]
        ], true);

        $response->assertStatus(200);
        $this->assertEquals(true, $responseData['success']);
        $this->assertEquals(2, $responseData['data']['parent_id']);
        $this->assertEquals(1, $responseData['data']['child_id']);
        $this->assertEquals(1, $responseData['data']['sequence']);
        $this->assertNotEmpty($responseData['data']['relative_time']['id']);
        $this->assertEquals(22, $responseData['data']['relative_time']['day']);
        $this->assertEquals('time_of_day', $responseData['data']['relative_time']['precision']);
        $this->assertEquals('AM', $responseData['data']['relative_time']['time_of_day']);
        $this->assertEquals('', $responseData['data']['relative_time']['hour']);
        $this->assertNull($responseData['data']['relative_time']['time']);
    }

    /**
     * @test
     */
    public function it_can_update_a_program_relation()
    {
        $programRelation = ProgramRelation::findOrFail(1);

        $this->assertEquals([
            'id' => 1,
            'parent_id' => 1,
            'child_id' => 2,
            'sequence' => 1,
            'relative_time' => [
                'id' => 1,
                'day' => 1,
                'precision' => 'time_of_day',
                'time_of_day' => 'AM',
                'hour' => '',
                'time' => null,
                'time_of_day_taxonomy' => [
                    'id' => 341,
                    'parent_id' => 340,
                    'name' => 'AM',
                    'priority' => 1,
                    'is_active' => true,
                    'is_required' => false,
                    'is_readonly' => true,
                    'is_merchantable' => false,
                    'is_searchable' => false,
                    'type' => 'unknown',
                    'icon' => null,
                    'translations' => [
                        'en' => 'AM',
                        'de' => 'Morgen',
                        'hu' => 'Délelőtt',
                        'ru' => 'утро',
                    ],
                ],
            ],
        ], (new ProgramRelationEntity($programRelation))->getFrontendData());

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, $responseData, $response) = $this->httpApiRequest('/admin/program-relation/1', 'PUT', $token, [
            'parent_id' => 2,
            'child_id' => 1,
            'sequence' => 1,
            'relative_time' => [
                'id' => 1,
                'day' => 3,
                'precision' => 'time_of_day',
                'time_of_day' => 'PM',
                'hour' => '',
                'time' => null
            ]
        ], true);

        $response->assertStatus(200);
        $this->assertEquals(true, $responseData['success']);
        $this->assertEquals(2, $responseData['data']['parent_id']);
        $this->assertEquals(1, $responseData['data']['child_id']);

        $programRelation = ProgramRelation::findOrFail(1);

        $this->assertEquals([
            'id' => 1,
            'parent_id' => 2,
            'child_id' => 1,
            'sequence' => 1,
            'relative_time' => [
                'id' => 1,
                'day' => 3,
                'precision' => 'time_of_day',
                'time_of_day' => 'PM',
                'hour' => '',
                'time' => null,
                'time_of_day_taxonomy' => [
                    'id' => 342,
                    'parent_id' => 340,
                    'name' => 'PM',
                    'priority' => 2,
                    'is_active' => true,
                    'is_required' => false,
                    'is_readonly' => true,
                    'is_merchantable' => false,
                    'is_searchable' => false,
                    'type' => 'unknown',
                    'icon' => null,
                    'translations' => [
                        'en' => 'PM',
                        'de' => 'Nachmittag',
                        'hu' => 'Délután',
                        'ru' => 'после полудня',
                    ],
                ],
            ],
        ], (new ProgramRelationEntity($programRelation))->getFrontendData());
    }

    /**
     * @test
     */
    public function it_can_delete_a_program_relation()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, $responseData, $response) = $this->httpApiRequest('/admin/program-relation/1', 'DELETE', $token, [], true);

        $response->assertStatus(200);
        $this->assertEquals(true, $responseData['success']);
        $this->assertNull(ProgramRelation::find(1));
    }

    /**
     * @test
     */
    public function it_can_change_the_program_relations_sequence()
    {
        $programs = $this->generatePrograms(3);

        $programRelations = [];
        for ($i = 1; $i < 3; $i++) {
            $programRelations[] = \App\ProgramRelation::create([
                'parent_id' => $programs[0]->id,
                'child_id'  => $programs[$i]->id,
                'sequence'  => 0,
                'relative_time_id' => 1
            ]);
        }

        $newSequence = [];
        $sequence = 1;
        foreach ($programRelations as $programRelation) {
            $newSequence[$programRelation->id] = [
                'id' => $programRelation->id,
                'sequence' => $sequence
            ];
            $sequence++;
        }

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, $responseData, $response) = $this->httpApiRequest(
            '/admin/program-relation/sequence',
            'PUT',
            $token,
            $newSequence,
            true
        );

        $response->assertStatus(200);
        $this->assertEquals(true, $responseData['success']);

        $programRelations = ProgramRelation::where('parent_id', $programs[0]->id)->get();
        foreach ($programRelations as $programRelation) {
            $this->assertEquals($newSequence[$programRelation->id]['sequence'], $programRelation->sequence);
        }
    }

    private function generatePrograms($count = 1): array
    {
        $programs = [];
        for ($i = 0; $i < $count; $i++) {
            $programs[] = factory(Program::class)->create(['location_id' => Location::first()->id]);
        }
        return $programs;
    }
}
