<?php

namespace Modules\Stylersauth\Tests\Integration\Models;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Modules\Stylersauth\Entities\Role;
use Modules\Stylersauth\Entities\User;
use Tests\TestCase;

class UserTest extends TestCase {

    /**
     * @test
     */
    function it_has_a_password_which_required() {
        $user = new User();
        $user->email = $this->faker->email;
        $user->name = $this->faker->name;
        $this->assertFalse($user->save());
        $this->assertNotEmpty($user);

        $user->password = $this->faker->word;
        $this->assertTrue($user->save());
    }

    /**
     * @test
     * @todo on some faker names(?) hashing fails to work, thus the test fails
     */
    function it_has_a_password_which_hashed() {
        $user = new User();
        $user->email = $this->faker->email;
        $user->name = $this->faker->name;
        $password = $this->faker->word;
        $user->password = $password;
        $user->save();

        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertFalse(Hash::check($this->faker->word, $user->password));
    }

    /**
     * @test
     */
    /* done at UserSetter
     * function email_field_is_unique() {
        $user = new User();
        $email = $this->faker->email;
        $user->email = $email;
        $user->name = $this->faker->name;
        $user->password = $this->faker->word;
        $this->assertTrue($user->saveOrFail());


        $userSecond = new User();
        $userSecond->email = $email;
        $userSecond->name = $this->faker->name;
        $userSecond->password = $this->faker->word;
        $this->assertFalse($userSecond->save());
        $this->assertNotEmpty($userSecond);
    }*/

    /**
     * @test
     */
    function it_can_have_role() {
        $user = new User();
        $user->email = $this->faker->email;
        $user->name = $this->faker->name;
        $user->password = $this->faker->word;
        $user->save();

        $role = Role::findOrFail(Config::get('stylersauth.role_admin'));
        $user->attachRole($role);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('manager'));
    }

    /**
     * @test
     */
    function email_attribute_saved_lowercase() {
        $email = strtoupper($this->faker->email);
        $user = new User();
        $user->email = $email;
        $user->name = $this->faker->name;
        $user->password = $this->faker->word;
        $user->save();

        $this->assertEquals(strtolower($email), $user->email);
        $this->assertFalse($user->hasRole('manager'));
    }

}
