<?php

namespace Tests\Functional\Controllers\Admin;

use App\Accommodation;
use App\Availability;
use App\Device;
use App\Entities\DeviceEntity;
use App\Facades\Config;
use App\Organization;
use App\OrganizationManager;
use Tests\TestCase;

class DeviceControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    private function prepare_models_and_entity()
    {
        $organization = Organization::findOrFail(1);
        $device = $organization->devices[0];
        return [$organization, $device, (new DeviceEntity($device))];
    }

    /**
     * @test
     * @group controller-write
     * @throws \Exception
     */
    public function it_can_list_devices()
    {
        list($token, $user) = $this->login([Config::getOrFail('stylersauth.role_manager')]);
        $deviceableType = Organization::class;
        $deviceableId = 3;

        list(, , $response) = $this->httpApiRequest(
            "/admin/device?deviceable_type={$deviceableType}&deviceable_id={$deviceableId}",
            'GET',
            $token
        );
        $response->assertStatus(403);

        $om = OrganizationManager::where([
            ['organization_id', '=', $deviceableId],
            ['user_id', '=', $user->id]
        ])->first();
        if (!$om) {
            $om = new OrganizationManager(['organization_id' => $deviceableId, 'user_id' => $user->id]);
            $this->assertTrue($om->save());
        }

        $responseData = $this->assertSuccessfulHttpApiRequest(
            "/admin/device?deviceable_type={$deviceableType}&deviceable_id={$deviceableId}",
            'GET',
            $token
        );

        $this->assertCount(1, $responseData->data);
        foreach ($responseData->data as $device) {
            $this->assertTrue(!!$device->id);
            $this->assertTrue(!!$device->name->en);
            $this->assertEquals(Organization::class, $device->deviceable_type);
            $this->assertEquals($deviceableId, $device->deviceable_id);
            $this->assertTrue(!!$device->usages);
        }
    }

    /**
     * @test
     * @group controller-write
     * @throws \Exception
     */
    public function it_can_get_a_device()
    {
        list($token,) = $this->login([Config::getOrFail('stylersauth.role_admin')]);
        list(, $device, $deviceEntity) = $this->prepare_models_and_entity();

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/device/{$device->id}", 'GET', $token, [], true);

        $frontendData = $deviceEntity->getFrontendData(['amount', 'descriptions', 'usages']);
        $data = $responseData['data'];
        $this->assertEquals($frontendData['id'], $data['id']);
        $this->assertEquals($frontendData['deviceable_type'], $data['deviceable_type']);
        $this->assertEquals($frontendData['deviceable_id'], $data['deviceable_id']);
        $this->assertEquals($frontendData['name'], $data['name']);
        $this->assertEquals($frontendData['type'], $data['type']);
        $this->assertEquals($frontendData['amount'], $data['amount']);
        $this->assertEquals($frontendData['descriptions'], $data['descriptions']);
        $this->assertEquals($frontendData['usages'], $data['usages']);
        $this->assertEquals($frontendData['usages'], $data['usages']);
    }

    /**
     * @test
     * @group controller-write
     * @throws \Exception
     */
    public function it_can_store_a_new_device()
    {
        list($token,) = $this->login([Config::getOrFail('stylersauth.role_admin')]);
        list($organization, ,) = $this->prepare_models_and_entity();

        $data = [
            'deviceable_type' => Organization::class,
            'deviceable_id' => $organization->id,
            'amount' => 10,
            'type' => 'room',
            'name' => [
                'en' => 'Test room'
            ],
            'short_description' => [
                'en' => 'Test room short description'
            ],
            'long_description' => [
                'en' => 'Test room long description'
            ],
            'usages' => []
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/device', 'POST', $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals(10, $responseData->data->amount);
        $this->assertEquals('room', $responseData->data->type);
        $this->assertEquals('Test room', $responseData->data->name->en);
    }

    /**
     * @test
     * @group controller-write
     * @throws \Exception
     */
    public function it_can_edit_a_device()
    {
        list($token,) = $this->login([Config::getOrFail('stylersauth.role_admin')]);
        list($organization, $device,) = $this->prepare_models_and_entity();

        $amountNew = 12;

        $data = [
            'deviceable_type' => Organization::class,
            'deviceable_id' => $organization->id,
            'amount' => $amountNew,
            'type' => 'room',
            'name' => [
                'en' => 'Test room'
            ],
            'short_description' => [
                'en' => 'Test room short description'
            ],
            'long_description' => [
                'en' => 'Test room long description'
            ],
            'usages' => []
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/device/{$device->id}", 'PUT', $token, $data);

        $this->assertEquals($device->id, $responseData->data->id);
        $this->assertEquals($amountNew, $responseData->data->amount);
        $this->assertEquals($data['name']['en'], $responseData->data->name->en);

        $availabilities = Availability::getAvailabilitiesToInfinity(Device::class, $device->id, date("Y-m-d"));
        foreach ($availabilities as $availability) {
            $this->assertEquals($amountNew, $availability->amount);
        }
    }

    /**
     * @test
     * @group controller-write
     * @throws \Exception
     */
    public function it_can_delete_a_device()
    {
        list($token,) = $this->login([Config::getOrFail('stylersauth.role_admin')]);
        list($organization, ,) = $this->prepare_models_and_entity();

        $data = [
            'deviceable_type' => Organization::class,
            'deviceable_id' => $organization->id,
            'amount' => 10,
            'type' => 'room',
            'name' => [
                'en' => 'Test room'
            ],
            'short_description' => [
                'en' => 'Test room short description'
            ],
            'long_description' => [
                'en' => 'Test room long description'
            ],
            'usages' => []
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/device', 'POST', $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $rangeToDel = $responseData->data;
        $responseDelData = $this->assertSuccessfulHttpApiRequest('/admin/device/' . $rangeToDel->id, 'DELETE', $token);

        $this->assertTrue(!!$responseDelData->data->id);
    }

    /**
     * @test
     * @group controller-write
     * @throws \Exception
     */
    public function it_can_get_channel_manager_id_list()
    {
        list($token) = $this->login([Config::getOrFail('stylersauth.role_admin')]);
        $deviceableType = Accommodation::class;
        $deviceableId = 1;

        $responseData = $this->assertSuccessfulHttpApiRequest(
            "/admin/device/channel-manager-ids?deviceable_type={$deviceableType}&deviceable_id={$deviceableId}",
            'GET',
            $token
        );
        $this->assertEmpty($responseData->data);


        $deviceableId = 21; //TODO mock HLS response
        $responseData = $this->assertSuccessfulHttpApiRequest(
            "/admin/device/channel-manager-ids?deviceable_type={$deviceableType}&deviceable_id={$deviceableId}",
            'GET',
            $token
        );

        $managedCount = 0;
        $this->assertNotEmpty($responseData->data);
        foreach ($responseData->data as $managedDevice) {
            $this->assertTrue(!!$managedDevice->channel_manager_id);
            $this->assertTrue(!!$managedDevice->channel_manager_name->en);
            if ($managedDevice->device) {
                $managedCount++;
                $this->assertTrue(!!$managedDevice->device->id);
                $this->assertTrue(!!$managedDevice->device->name->en);
            }
        }
        $this->assertGreaterThan(0, $managedCount);
    }

}
