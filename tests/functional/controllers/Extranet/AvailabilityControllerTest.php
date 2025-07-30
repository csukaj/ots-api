<?php

namespace Tests\Functional\Controllers\Extranet;

use App\AdminLog;
use App\Device;
use App\Organization;
use App\OrganizationManager;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AvailabilityControllerTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;
    const TEST_DATA_DIR = __DIR__ . '/AvailabilityControllerTestData/';

    private $token;
    private $user;
    private $organization;
    private $device;

    protected function prepareDevice(Device $device = null, $assignToUser = true, $loginAs = 'stylersauth.role_manager')
    {
        list($this->token, $this->user) = $this->login([Config::get($loginAs)]);

        if (is_null($device)) {
            $this->organization = factory(Organization::class, 'accommodation')->create();
            $this->device = factory(Device::class, 'room')->create([
                'deviceable_id' => $this->organization->id,
                'deviceable_type' => Organization::class,
                'amount' => 10
            ]);

        } else {
            $this->device = $device;
            $this->organization = $this->device->deviceable;
        }
        if ($assignToUser) {
            $existing = OrganizationManager::where([
                ['organization_id', '=', $this->organization->id],
                ['user_id', '=', $this->user->id]
            ])->count();
            if (!$existing) {
                factory(OrganizationManager::class)->create([
                    'organization_id' => $this->organization->id,
                    'user_id' => $this->user->id
                ]);
            }
        }
    }

    protected function setWithJson($requestJsonFile)
    {
        $requestBody = json_decode(file_get_contents(self::TEST_DATA_DIR . $requestJsonFile), true);
        $requestBody['availableType'] = Device::class;
        $requestBody['availableId'] = $this->device->id;

        $responseData = $this->assertSuccessfulHttpApiRequest(
            '/admin/availability', 'POST', $this->token, $requestBody
        );

        return $responseData;
    }

    protected function getWithJson($expectedResponseJsonFile, $fromDate, $toDate)
    {
        $expectedResponseBody = json_decode(file_get_contents(self::TEST_DATA_DIR . $expectedResponseJsonFile));

        $responseData = $this->assertSuccessfulHttpApiRequest(
            "/extranet/availability?availableType=App\\Device&availableId={$this->device->id}&fromDate={$fromDate}&toDate={$toDate}",
            'GET',
            $this->token, []
        );
        $this->assertEquals($expectedResponseBody, $responseData);

        return $responseData;
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_availability()
    {
        $this->prepareDevice();

        $responseData = $this->assertSuccessfulHttpApiRequest("/extranet/availability?availableType=App\\Device&availableId={$this->device->id}&fromDate=2026-01-01&toDate=2026-01-02",
            'GET', $this->token);

        $this->assertEquals(2, count($responseData->data));

        $this->assertEquals(2026, $responseData->data[0]->year);
        $this->assertEquals(1, $responseData->data[0]->month);
        $this->assertEquals(1, $responseData->data[0]->day);
        $this->assertEquals(10, $responseData->data[0]->amount);

        $this->assertEquals(2026, $responseData->data[1]->year);
        $this->assertEquals(1, $responseData->data[1]->month);
        $this->assertEquals(2, $responseData->data[1]->day);
        $this->assertEquals(10, $responseData->data[1]->amount);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_set_availability()
    {
        $this->prepareDevice();

        $this->assertSuccessfulHttpApiRequest(
            '/extranet/availability', 'POST', $this->token, [
                'availableType' => Device::class,
                'availableId' => $this->device->id,
                'availabilities' => [
                    ['year' => 2026, 'month' => 1, 'day' => 1, 'amount' => 2]
                ]
            ]
        );
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_an_availability_set()
    {
        $this->prepareDevice();

        $this->assertSuccessfulHttpApiRequest(
            '/extranet/availability', 'POST', $this->token, [
                'availableType' => Device::class,
                'availableId' => $this->device->id,
                'availabilities' => [
                    ['year' => 2026, 'month' => 1, 'day' => 2, 'amount' => 0]
                ]
            ]
        );

        $responseData = $this->assertSuccessfulHttpApiRequest("/extranet/availability?availableType=App\\Device&availableId={$this->device->id}&fromDate=2026-01-01&toDate=2026-01-03",
            'GET', $this->token);

        $this->assertEquals(3, count($responseData->data));

        $this->assertEquals(2026, $responseData->data[0]->year);
        $this->assertEquals(1, $responseData->data[0]->month);
        $this->assertEquals(1, $responseData->data[0]->day);
        $this->assertEquals(10, $responseData->data[0]->amount);

        $this->assertEquals(2026, $responseData->data[1]->year);
        $this->assertEquals(1, $responseData->data[1]->month);
        $this->assertEquals(2, $responseData->data[1]->day);
        $this->assertEquals(0, $responseData->data[1]->amount);

        $this->assertEquals(2026, $responseData->data[2]->year);
        $this->assertEquals(1, $responseData->data[2]->month);
        $this->assertEquals(3, $responseData->data[2]->day);
        $this->assertEquals(10, $responseData->data[2]->amount);

        $this->assertSuccessfulHttpApiRequest(
            '/extranet/availability', 'POST', $this->token, [
                'availableType' => Device::class,
                'availableId' => $this->device->id,
                'availabilities' => [
                    ['year' => 2026, 'month' => 1, 'day' => 2, 'amount' => 2]
                ]
            ]
        );

        $responseData = $this->assertSuccessfulHttpApiRequest("/extranet/availability?availableType=App\\Device&availableId={$this->device->id}&fromDate=2026-01-01&toDate=2026-01-03",
            'GET', $this->token);

        $this->assertEquals(3, count($responseData->data));

        $this->assertEquals(2026, $responseData->data[0]->year);
        $this->assertEquals(1, $responseData->data[0]->month);
        $this->assertEquals(1, $responseData->data[0]->day);
        $this->assertEquals(10, $responseData->data[0]->amount);

        $this->assertEquals(2026, $responseData->data[1]->year);
        $this->assertEquals(1, $responseData->data[1]->month);
        $this->assertEquals(2, $responseData->data[1]->day);
        $this->assertEquals(2, $responseData->data[1]->amount);

        $this->assertEquals(2026, $responseData->data[2]->year);
        $this->assertEquals(1, $responseData->data[2]->month);
        $this->assertEquals(3, $responseData->data[2]->day);
        $this->assertEquals(10, $responseData->data[2]->amount);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_cannot_get_a_device_of_a_accommodation_not_assigned()
    {
        $this->prepareDevice(null, false);

        list(, , $response) = $this->httpApiRequest("/extranet/availability?availableType=App\\Device&availableId={$this->device->id}&fromDate=2026-01-01&toDate=2026-01-02",
            'GET', $this->token);
        $response->assertStatus(403);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_cannot_set_a_device_of_a_accommodation_not_assigned()
    {
        $this->prepareDevice(null, false);

        list(, , $response) = $this->httpApiRequest(
            '/extranet/availability', 'POST', $this->token, [
                'availableType' => Device::class,
                'availableId' => $this->device->id,
                'availabilities' => [
                    ['year' => 2026, 'month' => 1, 'day' => 1, 'amount' => 2]
                ]
            ]
        );
        $response->assertStatus(403);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_availability_as_advisor()
    {
        $this->prepareDevice(null, true, 'stylersauth.role_advisor');

        $responseData = $this->assertSuccessfulHttpApiRequest("/extranet/availability?availableType=App\\Device&availableId={$this->device->id}&fromDate=2026-01-01&toDate=2026-01-02",
            'GET', $this->token);

        $this->assertEquals(2, count($responseData->data));

        $this->assertEquals(2026, $responseData->data[0]->year);
        $this->assertEquals(1, $responseData->data[0]->month);
        $this->assertEquals(1, $responseData->data[0]->day);
        $this->assertEquals(10, $responseData->data[0]->amount);

        $this->assertEquals(2026, $responseData->data[1]->year);
        $this->assertEquals(1, $responseData->data[1]->month);
        $this->assertEquals(2, $responseData->data[1]->day);
        $this->assertEquals(10, $responseData->data[1]->amount);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_cannot_set_a_device_of_a_accommodation_as_advisor()
    {
        $this->prepareDevice(null, false, 'stylersauth.role_advisor');

        list(, , $response) = $this->httpApiRequest(
            '/extranet/availability', 'POST', $this->token, [
                'availableType' => Device::class,
                'availableId' => $this->device->id,
                'availabilities' => [
                    ['year' => 2026, 'month' => 1, 'day' => 1, 'amount' => 2]
                ]
            ]
        );
        $response->assertStatus(403);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_log_availability_change()
    {
        $this->prepareDevice();

        $logCountBefore = AdminLog::count();
        $url = '/extranet/availability';
        $params = [
            'availableType' => Device::class,
            'availableId' => $this->device->id,
            'availabilities' => [
                ['year' => 2026, 'month' => 1, 'day' => 1, 'amount' => 2]
            ]
        ];

        $this->assertSuccessfulHttpApiRequest($url, 'POST', $this->token, $params);

        $this->assertEquals($logCountBefore + 1, AdminLog::count());
        $actual = AdminLog::latest()->first();
        $this->assertEquals($this->user->id, $actual->user_id);
        $this->assertEquals(ltrim($url, '/'), $actual->path);
        $this->assertEquals('store', $actual->action);
        $this->assertEquals($params, \json_decode($actual->request, true));
        $this->assertEquals(['success' => true], \json_decode($actual->response, true)['original']);
    }
}
