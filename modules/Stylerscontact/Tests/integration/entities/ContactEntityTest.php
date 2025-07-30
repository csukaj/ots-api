<?php

namespace Modules\Stylerscontact\Tests\Integration\Entities;

use Modules\Stylerscontact\Entities\Contact;
use Modules\Stylerscontact\Entities\ContactEntity;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class ContactEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models_and_entity(): array
    {
        $contact = Contact::first();
        return [$contact, (new ContactEntity($contact))];
    }

    /**
     * @test
     */
    function a_contact_has_data()
    {
        list($contact, $contactEntity) = $this->prepare_models_and_entity();

        $contactData = $contactEntity->getFrontendData();
        $this->assertEquals($contact->id, $contactData['id']);
        $this->assertEquals((new TaxonomyEntity($contact->type))->getFrontendData(['translations']),
            $contactData['type']);
        $this->assertEquals($contact->value, $contactData['value']);
        $this->assertEquals($contact->is_public, $contactData['is_public']);
    }

    /**
     * @test
     */
    function a_contact_has_contactable_data()
    {
        list($contact, $contactEntity) = $this->prepare_models_and_entity();

        $contactData = $contactEntity->getFrontendData(['contactable']);
        $this->assertEquals(get_class($contact->contactable), $contactData['contactable_type']);
        $this->assertEquals($contact->contactable->id, $contactData['contactable_id']);

    }


}
