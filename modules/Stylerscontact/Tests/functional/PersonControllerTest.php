<?php

namespace Modules\StylersContact\Tests\Functional\Controllers;

use App\Facades\Config;
use App\Supplier;
use Modules\Stylerscontact\Entities\Person;
use Modules\Stylerscontact\Entities\PersonEntity;
use Tests\TestCase;

class PersonControllerTest extends TestCase
{

    private function prepare_models_and_entity(): array
    {
        $person = Person::first();
        return [$person, (new PersonEntity($person))];
    }

    /**
     * @test
     */
    public function it_can_list_persons()
    {
        $personable_id = Supplier::first()->id;
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        list(, $responseData, $response) = $this->httpApiRequest('/stylerscontact/person?personable_type=' . Supplier::class . '&personable_id=' . $personable_id,
            'GET', $token, [], true);

        $allPerson = PersonEntity::getCollection(Person::forPersonable(Supplier::class, $personable_id));

        $response->assertStatus(200);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($allPerson, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_get_a_person()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($person, $personEntity) = $this->prepare_models_and_entity();

        list(, $responseData, $response) = $this->httpApiRequest("/stylerscontact/person/{$person->id}", 'GET', $token,
            [], true);
        $response->assertStatus(200);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($personEntity->getFrontendData(['personable', 'contacts']), $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_store_a_new_person()
    {
        list($token, $user) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = [
            'personable_type' => Supplier::class,
            'personable_id' => Supplier::first()->id,
            'name' => $this->faker->name
        ];

        list(, $responseData, $response) = $this->httpApiRequest('/stylerscontact/person', 'POST', $token, $data);
        $response->assertStatus(200);
        $this->assertTrue($responseData->success);
        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($data['name'], $responseData->data->name);
    }

    /**
     * @test
     */
    public function it_can_edit_a_person()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($person,) = $this->prepare_models_and_entity();

        $data = [
            'id' => $person->id,
            'personable_type' => Supplier::class,
            'personable_id' => Supplier::first()->id,
            'name' => $this->faker->name
        ];

        list(, $responseData, $response) = $this->httpApiRequest("/stylerscontact/person/{$person->id}", 'PUT', $token,
            $data);

        $response->assertStatus(200);
        $this->assertTrue($responseData->success);
        $this->assertEquals($person->id, $responseData->data->id);
        $this->assertEquals($data['name'], $responseData->data->name);
    }

    /**
     * @test
     */
    public function it_can_delete_a_person()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = [
            'personable_type' => Supplier::class,
            'personable_id' => Supplier::first()->id,
            'name' => $this->faker->name
        ];

        list(, $createResponseData, $createResponse) = $this->httpApiRequest('/stylerscontact/person', 'POST', $token,
            $data);

        $createResponse->assertStatus(200);
        $this->assertTrue($createResponseData->success);
        $this->assertTrue(!!$createResponseData->data->id);
        $id = $createResponseData->data->id;

        list(, $responseData, $response) = $this->httpApiRequest("/stylerscontact/person/{$id}", 'DELETE', $token);

        $response->assertStatus(200);
        $this->assertTrue($responseData->success);
        $this->assertNotEmpty(Person::onlyTrashed()->find($id));
    }
}
