<?php

namespace Tests\Functional\Controllers\Admin;

use App\Accommodation;
use App\Entities\OrganizationEntity;
use App\Entities\UserEntity;
use App\Organization;
use App\User;
use Illuminate\Support\Facades\Config;
use Modules\Stylersauth\Entities\Role;
use Modules\Stylersauth\Entities\RoleEntity;
use Tests\TestCase;

class UserControllerTest extends TestCase
{


    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_users()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $users = UserEntity::getCollection(User::orderBy('name', 'ASC')->get(), ['roles', 'organizations', 'sites']);
        $organizations = Accommodation::getEnglishNames()->toArray();

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/user", 'GET', $token, [], true);

        $this->assertEquals($users, $responseData['data']);
        $this->assertEquals($organizations, $responseData['organizations']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_show_an_user()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $id = User::first()->id;
        $user = (new UserEntity(User::findOrFail($id)))->getFrontendData(['roles', 'organizations', 'sites']);
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/user/{$id}", 'GET', $token, [], true);

        $this->assertEquals($user, $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_an_user()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = [
            "roles" => ["manager"],
            "organizations" => [1, 13],
            "name" => "User create test",
            "email" => "test-create@example.com"
        ];

        $role = (new RoleEntity(Role::where('name', $data['roles'][0])->first()))->getFrontendData();
        $organizations = OrganizationEntity::getCollection(Organization::find($data['organizations']));

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/user', 'POST', $token, $data, true);

        $this->assertTrue(!!$responseData['data']['id']);
        $this->assertEquals($data['name'], $responseData['data']['name']);
        $this->assertEquals($data['email'], $responseData['data']['email']);
        $this->assertEquals($role, $responseData['data']['roles'][0]);
        $this->assertEquals($organizations, $responseData['data']['organizations']);

    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_update_an_user()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = [
            "roles" => ["manager"],
            "organizations" => [1, 13],
            "name" => "User create test",
            "email" => "test-create@example.com"
        ];

        $responseCreateData = $this->assertSuccessfulHttpApiRequest('/admin/user', 'POST', $token, $data);

        $data = [
            "name" => "User create test UPDATE",
            "email" => "test-create-update@example.com",
            "roles" => ["admin"],
            "organizations" => []
        ];

        $role = (new RoleEntity(Role::where('name', $data['roles'][0])->first()))->getFrontendData();
        $organizations = OrganizationEntity::getCollection(Organization::find($data['organizations']));


        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/user/' . $responseCreateData->data->id, 'PUT',
            $token, $data, true);

        $this->assertTrue(!!$responseData['data']['id']);
        $this->assertEquals($data['name'], $responseData['data']['name']);
        $this->assertEquals($data['email'], $responseData['data']['email']);
        $this->assertEquals($role, $responseData['data']['roles'][0]);
        $this->assertEquals($organizations, $responseData['data']['organizations']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_delete_an_user()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = [
            "roles" => ["manager"],
            "organizations" => [1, 13],
            "name" => "User create test",
            "email" => "test-create3@example.com"
        ];

        $responseCreateData = $this->assertSuccessfulHttpApiRequest('/admin/user', 'POST', $token, $data);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/user/' . $responseCreateData->data->id,
            'DELETE', $token);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertNotEmpty(User::onlyTrashed()->find($responseData->data->id));
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_save_user_email_lowercased()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = [
            "roles" => ["manager"],
            "organizations" => [1, 13],
            "name" => "User create test",
            "email" => "TEST-creaTE@example.com"
        ];

        $responseCreateData = $this->assertSuccessfulHttpApiRequest('/admin/user', 'POST', $token, $data);
        $this->assertEquals(strtolower($data['email']), $responseCreateData->data->email);

        $data = [
            "name" => "User create test UPDATE",
            "email" => "TEST-create-UPDATE@example.com",
            "roles" => ["admin"],
            "organizations" => []
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/user/' . $responseCreateData->data->id, 'PUT',
            $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals(strtolower($data['email']), $responseData->data->email);

    }


}
