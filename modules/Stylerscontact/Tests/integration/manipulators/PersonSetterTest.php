<?php

namespace Tests\Integration\Manipulators;

use App\Exceptions\UserException;
use App\Supplier;
use Modules\Stylerscontact\Entities\Person;
use Modules\Stylerscontact\Manipulators\PersonSetter;
use Tests\TestCase;

class PersonSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_save_person()
    {
        $data = [
            'personable_type' => Supplier::class,
            'personable_id' => Supplier::first()->id,
            'name' => $this->faker->name
        ];

        $person = (new PersonSetter($data))->set();
        $this->assertInstanceOf(Person::class, $person);
        $this->assertEquals($data['name'], $person->name);
    }

    /**
     * @test
     */
    function it_cant_save_person_with_invalid_data()
    {
        $data = [
            'personable_type' => Supplier::class,
            'personable_id' => Supplier::first()->id
        ];

        $this->expectException(UserException::class);
        (new PersonSetter(['personable_type' => Supplier::class, 'name' => 'a']))->set();
        (new PersonSetter($data))->set();
    }


    /**
     * @test
     */
    function it_can_update_person()
    {
        $data = [
            'personable_type' => Supplier::class,
            'personable_id' => Supplier::first()->id,
            'name' => $this->faker->name
        ];

        $person = (new PersonSetter($data))->set();
        $this->assertInstanceOf(Person::class, $person);

        $update = [
            'id' => $person->id,
            'personable_type' => Supplier::class,
            'personable_id' => Supplier::first()->id,
            'name' => 'Fake User'
        ];

        $updatedPerson = (new PersonSetter($update))->set();
        $this->assertInstanceOf(Person::class, $person);
        $this->assertEquals($person->id, $updatedPerson->id);
        $this->assertEquals($update['name'], $updatedPerson->name);
    }
}
