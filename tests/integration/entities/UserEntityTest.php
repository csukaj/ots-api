<?php

namespace Tests\Integration\Entities;

use App\Entities\UserEntity;
use App\User;
use Tests\TestCase;

class UserEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity()
    {
        $user = User::firstOrFail();
        return [$user, (new UserEntity($user))];
    }

    /**
     * @test
     */
    function it_can_present_user_data()
    {
        list($user, $userEntity) = $this->prepare_model_and_entity();
        /** @var UserEntity $userEntity */
        $frontendData = $userEntity->getFrontendData();

        $this->assertEquals($user->id, $frontendData['id']);
        $this->assertEquals($user->email, $frontendData['email']);
        $this->assertEquals($user->name, $frontendData['name']);
    }

    /**
     * @test
     */
    function it_can_present_user_data_with_roles()
    {
        list($user, $userEntity) = $this->prepare_model_and_entity();
        /** @var UserEntity $userEntity */
        $frontendData = $userEntity->getFrontendData(['roles']);

        $this->assertEquals($user->id, $frontendData['id']);
        $this->assertEquals(count($user->roles), count($frontendData['roles']));

    }

    /**
     * @test
     */
    function it_can_present_user_data_with_organizations()
    {
        list($user, $userEntity) = $this->prepare_model_and_entity();
        /** @var UserEntity $userEntity */
        $frontendData = $userEntity->getFrontendData(['organizations']);

        $this->assertEquals($user->id, $frontendData['id']);
        $this->assertEquals(count($user->organizations), count($frontendData['organizations']));

    }

    /**
     * @test
     */
    function it_can_present_user_data_with_sites()
    {
        $user = User::withRole('advisor')->firstOrFail();
        $userEntity = new UserEntity($user);
        $frontendData = $userEntity->getFrontendData(['sites']);

        $this->assertEquals($user->id, $frontendData['id']);
        $this->assertGreaterThan(0, count($frontendData['sites']));
        $this->assertEquals($user->sites->pluck('site')->toArray(), $frontendData['sites']);

    }
}
