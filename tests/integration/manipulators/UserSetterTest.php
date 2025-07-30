<?php

namespace Tests\Integration\Manipulators;

use App\Manipulators\UserSetter;
use App\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserSetterTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_save_admin_user() {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->word,
            'roles' => ['admin']
        ];

        $userSetter = new UserSetter($data);
        $user = $userSetter->set();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->assertEquals($data['roles'], $user->roles()->get()->pluck('name')->all());
    }

    /**
     * @test
     */
    function it_can_save_manager_user_with_one_organization() {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->word,
            'roles' => ['manager'],
            'organizations' => [1]
        ];

        $userSetter = new UserSetter($data);
        $user = $userSetter->set();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->assertEquals($data['roles'], $user->roles()->get()->pluck('name')->all());
        $this->assertEquals($data['organizations'], $user->organizations()->get()->pluck('id')->all());
    }


    /**
     * @test
     */
    function it_can_save_manager_user_with_more_organization() {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->word,
            'roles' => ['manager'],
            'organizations' => [1,2,3]
        ];

        $userSetter = new UserSetter($data);
        $user = $userSetter->set();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->assertEquals($data['roles'], $user->roles()->get()->pluck('name')->all());
        $this->assertEquals($data['organizations'], $user->organizations()->orderBy('id')->get()->pluck('id')->all());
        $this->assertTrue($user->hasRole('manager'));
    }

    /**
     * @test
     */
    function it_can_update_user() {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->word,
            'roles' => ['admin']
        ];

        $user = (new UserSetter($data))->set();
        $this->assertInstanceOf(User::class, $user);

        $update = [
            'id' => $user->id,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->word,
            'roles' => ['admin']
        ];

        $updatedUser = (new UserSetter($update))->set();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->id, $updatedUser->id);
        $this->assertEquals($update['name'], $updatedUser->name);
        $this->assertEquals($update['email'], $updatedUser->email);
        $this->assertTrue(Hash::check($update['password'], $updatedUser->password));
        $this->assertTrue($updatedUser->hasRole('admin'));
    }

    /**
     * @test
     */
    function it_can_save_advisor_user_with_sites() {
        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->word,
            'roles' => ['advisor'],
            'sites' => ['seychelle-szigetek.hu','localhost']
        ];

        $userSetter = new UserSetter($data);
        $user = $userSetter->set();
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['sites'], $user->sites()->get()->pluck('site')->all());
    }

}
