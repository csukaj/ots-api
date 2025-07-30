<?php

namespace Tests\Functional\Controllers\Extranet;

use App\Availability;
use App\Device;
use App\Entities\DeviceEntity;
use App\Organization;
use App\OrganizationManager;
use Illuminate\Support\Facades\Config;
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
     */
    public function it_can_list_devices()
    {
        list($token, $user) = $this->login([Config::get('stylersauth.role_manager')]);
        $deviceableType = Organization::class;
        $deviceableId = 3;

        list(, , $response) = $this->httpApiRequest(
            "/extranet/device?deviceable_type={$deviceableType}&deviceable_id={$deviceableId}",
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
            "/extranet/device?deviceable_type={$deviceableType}&deviceable_id={$deviceableId}",
            'GET',
            $token
        );

        $this->assertEquals(1, count($responseData->data));
        foreach ($responseData->data as $device) {
            $this->assertTrue(!!$device->id);
            $this->assertTrue(!!$device->name->en);
            $this->assertEquals(Organization::class, $device->deviceable_type);
            $this->assertEquals($deviceableId, $device->deviceable_id);
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_a_device()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
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
}
