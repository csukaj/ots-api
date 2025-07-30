<?php

namespace Modules\StylersContact\Tests\Functional\Controllers;

use App\Facades\Config;
use App\Supplier;
use Modules\Stylerscontact\Entities\Contact;
use Modules\Stylerscontact\Entities\ContactEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{

    private function prepare_models_and_entity(): array
    {
        $contact = Contact::first();
        return [$contact, (new ContactEntity($contact))];
    }

    /**
     * @test
     */
    public function it_can_list_contacts()
    {
        $contactable_id = Supplier::first()->id;
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        list(, $responseData, $response) = $this->httpApiRequest('/stylerscontact/contact?contactable_type=' . Supplier::class . '&contactable_id=' . $contactable_id,
            'GET', $token, [], true);

        $allContact = ContactEntity::getCollection(Contact::forContactable(Supplier::class, $contactable_id));

        $response->assertStatus(200);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($allContact, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_get_a_contact()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($contact, $contactEntity) = $this->prepare_models_and_entity();

        list(, $responseData, $response) = $this->httpApiRequest("/stylerscontact/contact/{$contact->id}", 'GET', $token,
            [], true);
        $response->assertStatus(200);
        $this->assertTrue($responseData['success']);
        $this->assertEquals($contactEntity->getFrontendData(['contactable', 'contacts']), $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_store_a_new_contact()
    {
        list($token, $user) = $this->login([Config::get('stylersauth.role_admin')]);

        $tx = Taxonomy::findOrFail(Config::getOrFail('taxonomies.contact_types.email'));
        $data = [
            'contactable_type' => Supplier::class,
            'contactable_id' => Supplier::first()->id,
            'type' =>(new TaxonomyEntity($tx))->getFrontendData(['translations']),
            'value' => $this->faker->email,
            'is_public' => false
        ];

        list(, $responseData, $response) = $this->httpApiRequest('/stylerscontact/contact', 'POST', $token, $data);
        $response->assertStatus(200);
        $this->assertTrue($responseData->success);
        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($data['value'], $responseData->data->value);
    }

    /**
     * @test
     */
    public function it_can_edit_a_contact()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($contact,) = $this->prepare_models_and_entity();

        $tx = Taxonomy::findOrFail(Config::getOrFail('taxonomies.contact_types.email'));
        $data = [
            "id" => $contact->id,
            'contactable_type' => Supplier::class,
            'contactable_id' => Supplier::first()->id,
            'type' =>(new TaxonomyEntity($tx))->getFrontendData(['translations']),
            'value' => 'root_update@example.com',
            'is_public' => true
        ];

        list(, $responseData, $response) = $this->httpApiRequest("/stylerscontact/contact/{$contact->id}", 'PUT', $token,
            $data);

        $response->assertStatus(200);
        $this->assertTrue($responseData->success);
        $this->assertEquals($contact->id, $responseData->data->id);
        $this->assertEquals($data['value'], $responseData->data->value);
    }

    /**
     * @test
     */
    public function it_can_delete_a_contact()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $tx = Taxonomy::findOrFail(Config::getOrFail('taxonomies.contact_types.email'));
        $data = [
            'contactable_type' => Supplier::class,
            'contactable_id' => Supplier::first()->id,
            'type' =>(new TaxonomyEntity($tx))->getFrontendData(['translations']),
            'value' => $this->faker->email,
            'is_public' => false
        ];

        list(, $createResponseData, $createResponse) = $this->httpApiRequest('/stylerscontact/contact', 'POST', $token,
            $data);

        $createResponse->assertStatus(200);
        $this->assertTrue($createResponseData->success);
        $this->assertTrue(!!$createResponseData->data->id);
        $id = $createResponseData->data->id;

        list(, $responseData, $response) = $this->httpApiRequest("/stylerscontact/contact/{$id}", 'DELETE', $token);

        $response->assertStatus(200);
        $this->assertTrue($responseData->success);
        $this->assertNotEmpty(Contact::onlyTrashed()->find($id));
    }
}
