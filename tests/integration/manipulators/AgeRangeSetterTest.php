<?php

namespace Tests\Integration\Manipulators;

use App\AgeRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Manipulators\AgeRangeSetter;
use App\Organization;
use Tests\TestCase;

class AgeRangeSetterTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ONCE;

    protected function prepare_organization()
    {
        return factory(Organization::class, 'accommodation')->create();
    }

    /**
     * @test
     */
    function it_can_be_set()
    {
        $organization = $this->prepare_organization();

        $data = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 1,
            'to_age' => 3,
            'name_taxonomy' => 'child'
        ];

        $ageRange = (new AgeRangeSetter($data))->set();

        $this->assertTrue(!!$ageRange->id);
        $this->assertEquals($data['from_age'], $ageRange->from_age);
        $this->assertEquals($data['to_age'], $ageRange->to_age);
        $this->assertEquals($data['age_rangeable_type'], $ageRange->age_rangeable_type);
        $this->assertEquals($data['age_rangeable_id'], $ageRange->age_rangeable_id);
        $this->assertEquals(Config::get("taxonomies.age_ranges." . $data['name_taxonomy'])['id'], $ageRange->name_taxonomy_id);
    }

    /**
     * @test
     */
    function it_can_create_new_age_range_taxonomy()
    {
        $organization = $this->prepare_organization();

        $data = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 10,
            'to_age' => 16,
            'name_taxonomy' => 'teenager'
        ];

        $ageRange = (new AgeRangeSetter($data))->set();

        $this->assertTrue(!!$ageRange->id);
        $this->assertEquals(Config::get("taxonomies.age_range"), $ageRange->name->parent_id);
        $this->assertEquals($data['name_taxonomy'], $ageRange->name->name);
    }

    /**
     * @test
     * For overlaps see @file: tests/integration/models/AgeRangeTest.php
     */
    function it_cannot_be_set_overlapped()
    {
        $organization = $this->prepare_organization();

        $data = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 1,
            'to_age' => 3,
            'name_taxonomy' => 'child'
        ];

        $ageRangeOne = new AgeRangeSetter($data);
        $ageRangeOne->set();

        $this->expectException(UserException::class);
        $ageRangeTwo = new AgeRangeSetter($data);
        $ageRangeTwo->set();
    }

    /**
     * @test
     */
    function it_can_updated()
    {
        $organization = $this->prepare_organization();

        $data = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 1,
            'to_age' => 3,
            'name_taxonomy' => 'child'
        ];

        $ageRangeSetter = new AgeRangeSetter($data);
        $ageRange = $ageRangeSetter->set();

        $data['id'] = $ageRange->id;
        $data['to_age'] = 4;
        $ageRangeUpdated = (new AgeRangeSetter($data))->set();
        $this->assertEquals($ageRange->id, $ageRangeUpdated->id);
        $this->assertEquals($data['to_age'], $ageRangeUpdated->to_age);
    }

    /**
     * @test
     */
    function it_can_set_infinite()
    {
        $organization = $this->prepare_organization();
        $nameDescription = $this->faker->word;

        $data = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 0,
            'to_age' => 3,
            'name_taxonomy' => 'child'
        ];

        $ageRangeSetter = new AgeRangeSetter($data);
        $ageRange = $ageRangeSetter->set();

        $infData = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 4,
            'to_age' => null,
            'name_taxonomy' => 'adult'
        ];

        $infAgeRangeSetter = new AgeRangeSetter($infData);
        $infAgeRange = $infAgeRangeSetter->set();

        $this->assertNull($infAgeRange->to_age);
    }

    /**
     * @test
     */
    function it_cannot_set_same_name()
    {
        $organization = $this->prepare_organization();

        $data = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 0,
            'to_age' => 3,
            'name_taxonomy' => 'child'
        ];

        $ageRangeSetter = new AgeRangeSetter($data);
        $ageRange = $ageRangeSetter->set();
        $this->assertInstanceOf(AgeRange::class, $ageRange);

        $infData = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 4,
            'to_age' => 5,
            'name_taxonomy' => 'child'
        ];

        $this->expectException(UserException::class);
        $infAgeRangeSetter = new AgeRangeSetter($infData);
        $infAgeRangeSetter->set();
    }

    /**
     * @test
     */
    function it_cannot_set_ends_of_period()
    {
        $organization = $this->prepare_organization();

        $data = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 0,
            'to_age' => 3,
            'name_taxonomy' => 'child'
        ];

        $ageRangeSetter = new AgeRangeSetter($data);
        $ageRange = $ageRangeSetter->set();
        $this->assertInstanceOf(AgeRange::class, $ageRange);

        $infData = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 3,
            'to_age' => 12,
            'name_taxonomy' => 'teenage'
        ];

        $this->expectException(UserException::class);
        $infAgeRangeSetter = new AgeRangeSetter($infData);
        $infAgeRangeSetter->set();
    }

    /**
     * @test
     */
    function it_cannot_modify_adult_name()
    {
        $organization = $this->prepare_organization();

        $data = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 0,
            'to_age' => null,
            'name_taxonomy' => 'adult'
        ];

        $ageRange = (new AgeRangeSetter($data))->set();
        $this->assertInstanceOf(AgeRange::class, $ageRange);

        $infData = [
            'age_rangeable_type' => Organization::class,
            'age_rangeable_id' => $organization->id,
            'from_age' => 0,
            'to_age' => null,
            'name_taxonomy' => 'child'
        ];

        $this->expectException(UserException::class);
        (new AgeRangeSetter($infData))->set();
    }
}