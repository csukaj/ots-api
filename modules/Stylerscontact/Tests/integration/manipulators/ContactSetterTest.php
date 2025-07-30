<?php

namespace Tests\Integration\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\Supplier;
use Modules\Stylerscontact\Entities\Contact;
use Modules\Stylerscontact\Manipulators\ContactSetter;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class ContactSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_save_contact()
    {
        $tx = Taxonomy::findOrFail(Config::getOrFail('taxonomies.contact_types.email'));
        $data = [
            'contactable_type' => Supplier::class,
            'contactable_id' => Supplier::first()->id,
            'type' =>(new TaxonomyEntity($tx))->getFrontendData(['translations']),
            'value' => $this->faker->email,
            'is_public' => false
        ];

        $contact = (new ContactSetter($data))->set();
        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertEquals($data['value'], $contact->value);
    }

    /**
     * @test
     */
    function it_cant_save_contact_with_invalid_data()
    {
        $tx = Taxonomy::findOrFail(Config::getOrFail('taxonomies.organization_types.hotel_chain.id'));

        $data = [
            'contactable_type' => Supplier::class,
            'contactable_id' => Supplier::first()->id,
            'type' =>(new TaxonomyEntity($tx))->getFrontendData(['translations']),
            'value' => $this->faker->email,
            'is_public' => false
        ];

        $this->expectException(UserException::class);
        (new ContactSetter([]))->set();
        (new ContactSetter($data))->set();
    }


    /**
     * @test
     */
    function it_can_update_contact()
    {
        $tx = Taxonomy::findOrFail(Config::getOrFail('taxonomies.contact_types.email'));
        $data = [
            'contactable_type' => Supplier::class,
            'contactable_id' => Supplier::first()->id,
            'type' =>(new TaxonomyEntity($tx))->getFrontendData(['translations']),
            'value' => $this->faker->email,
            'is_public' => false
        ];

        $contact = (new ContactSetter($data))->set();
        $this->assertInstanceOf(Contact::class, $contact);

        $update = [
            "id" => $contact->id,
            'contactable_type' => Supplier::class,
            'contactable_id' => Supplier::first()->id,
            'type' =>(new TaxonomyEntity($tx))->getFrontendData(['translations']),
            'value' => 'root_update@example.com',
            'is_public' => true
        ];

        $updatedContact = (new ContactSetter($update))->set();
        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertEquals($contact->id, $updatedContact->id);
        $this->assertEquals($update['value'], $updatedContact->value);
        $this->assertEquals($update['is_public'], $updatedContact->is_public);
    }
}
