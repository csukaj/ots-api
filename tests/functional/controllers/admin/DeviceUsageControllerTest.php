<?php

namespace Tests\Functional\Controllers\Admin;

use App\Device;
use App\DeviceUsage;
use App\DeviceUsageElement;
use App\Organization;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DeviceUsageControllerTest extends TestCase {
    
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    private $adminToken;

    public function setUp(): void {
        parent::setUp();
        list($this->adminToken, ) = $this->login([Config::get('stylersauth.role_admin')]);
    }

    protected function generateSample() {
        $device = Device::where('deviceable_type', '=', Organization::class)->first();
        $requestBody = [
            'device_id' => $device->id,
            'elements' => [['amount' => rand(1, 10), 'age_range' => ['id' => $device->deviceable->ageRanges[0]->id]]]
        ];
        return $requestBody;
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_be_created() {
        $requestBody = $this->generateSample();
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/device-usage', 'POST', $this->adminToken, $requestBody);

        $this->assertEquals($requestBody['device_id'], $responseData->data->device_id);
        $this->assertEquals(count($requestBody['elements']), count($responseData->data->elements));
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_be_updated() {
        $requestBody = $this->generateSample();
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/device-usage', 'POST', $this->adminToken, $requestBody);

        $requestBody['elements'][0]['id'] = $responseData->data->elements[0]->id;
        $requestBody['elements'][0]['amount'] = rand(1, 10);
        $responseData2 = $this->assertSuccessfulHttpApiRequest("/admin/device-usage/{$responseData->data->id}", 'PUT', $this->adminToken, $requestBody);

        $this->assertEquals(count($requestBody['elements']), count($responseData2->data->elements));
        $this->assertEquals($requestBody['elements'][0]['amount'], $responseData2->data->elements[0]->amount);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_be_shown() {
        $requestBody = $this->generateSample();
        $responseDataCreate = $this->assertSuccessfulHttpApiRequest('/admin/device-usage', 'POST', $this->adminToken, $requestBody);

        $responseDataShow = $this->assertSuccessfulHttpApiRequest("/admin/device-usage/{$responseDataCreate->data->id}", 'GET', $this->adminToken);
        $this->assertEquals($responseDataCreate->data->device_id, $responseDataShow->data->device_id);
        $this->assertEquals(count($responseDataCreate->data->elements), count($responseDataShow->data->elements));
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_be_deleted() {
        $deviceUsageCountBefore = DeviceUsage::all()->count();
        
        // create a new usage
        $responseDataCreate = $this->assertSuccessfulHttpApiRequest('/admin/device-usage', 'POST', $this->adminToken, $this->generateSample());

        // delete that usage
        $responseDataDel = $this->assertSuccessfulHttpApiRequest("/admin/device-usage/{$responseDataCreate->data->id}", 'DELETE', $this->adminToken);
        $this->assertEquals($responseDataCreate->data->id, $responseDataDel->data->id);
        $this->assertEquals($responseDataCreate->data->device_id, $responseDataDel->data->device_id);

        // check counts
        $deviceUsageCountAfter = DeviceUsage::all()->count();
        $this->assertEquals($deviceUsageCountBefore, $deviceUsageCountAfter);
        $this->assertEquals(null, DeviceUsage::find($responseDataCreate->data->id));
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_be_listed() {
        $requestBody1 = $this->generateSample();
        $responseDataCreate1 = $this->assertSuccessfulHttpApiRequest('/admin/device-usage', 'POST', $this->adminToken, $requestBody1);
        $requestBody2 = $this->generateSample();
        $responseDataCreate2 = $this->assertSuccessfulHttpApiRequest('/admin/device-usage', 'POST', $this->adminToken, $requestBody2);

        $responseDataList = $this->assertSuccessfulHttpApiRequest('/admin/device-usage', 'GET', $this->adminToken);

        $foundObjects = array_filter(
            $responseDataList->data, function ($element) use ($responseDataCreate1, $responseDataCreate2) {
            return in_array($element->id, [$responseDataCreate1->data->id, $responseDataCreate2->data->id]);
        }
        );
        $this->assertEquals(2, count($foundObjects));
    }

}