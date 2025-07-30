<?php

namespace Modules\Stylerscontact\Tests\Integration\Entities;

use Modules\Stylerscontact\Entities\ContactEntity;
use Modules\Stylerscontact\Entities\Person;
use Modules\Stylerscontact\Entities\PersonEntity;
use Tests\TestCase;

class PersonEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models_and_entity(): array
    {
        $person = Person::first();
        return [$person, (new PersonEntity($person))];
    }

    /**
     * @test
     */
    function a_person_has_data()
    {
        list($person, $personEntity) = $this->prepare_models_and_entity();

        $personData = $personEntity->getFrontendData();
        $this->assertEquals($person->id, $personData['id']);
        $this->assertEquals($person->name, $personData['name']);
    }

    /**
     * @test
     */
    function a_person_has_extended_data()
    {
        list($person, $personEntity) = $this->prepare_models_and_entity();

        $personData = $personEntity->getFrontendData(['personable','contacts']);
        $this->assertEquals(get_class($person->personable), $personData['personable_type']);
        $this->assertEquals($person->personable->id, $personData['personable_id']);
        $this->assertEquals(ContactEntity::getCollection($person->contacts), $personData['contacts']);

    }


}
