<?php

namespace Tests\Functional\Controllers\Admin;

use App\Device;
use App\DeviceMinimumNights;
use App\Entities\DeviceMinimumNightsEntity;
use App\Organization;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DeviceMinimumNightsControllerTest extends TestCase {

    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;
    private $adminToken;

    public function setUp() {
        parent::setUp();
        list($this->adminToken, ) = $this->login([Config::get('stylersauth.role_admin')]);
    }

    protected function prepare() {
        $device = Device::where('deviceable_type', '=', Organization::class)->orderBy('id')->first();
        return [$device, $device->deviceable, $device->deviceable->dateRanges()->open()->first()];
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_be_created() {
        list($device, $organization, $dateRange) = $this->prepare();
        $requestBody = [
            'organization_id' => $organization->id,
            'minimum_nights' => [
                [
                    'device_id' => $device->id,
                    'date_range_id' => $dateRange->id,
                    'minimum_nights' => 5
                ]
            ]
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/device-minimum-nights', 'POST', $this->adminToken, $requestBody, true);

        $this->assertCount(count($requestBody['minimum_nights']), $responseData['data']);
        foreach ($requestBody['minimum_nights'] as $idx => $item) {
            $this->assertEquals($item['device_id'], $responseData['data'][$idx]['device_id']);
            $this->assertEquals($item['date_range_id'], $responseData['data'][$idx]['date_range_id']);
            $this->assertEquals($item['minimum_nights'], $responseData['data'][$idx]['minimum_nights']);
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_be_updated() {
        list(, $organization) = $this->prepare();
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/device-minimum-nights?organization_id=' . $organization->id, 'GET', $this->adminToken, [], true);

        $idsorterFunc = function($a, $b) {
            return $a['id'] - $b['id'];
        };

        usort($responseData['data'], $idsorterFunc);

        $updateData = $responseData['data'];
        unset($updateData[count($updateData) - 1]);
        $updateData[0]['minimum_nights'] = 99;

        $requestBody = ['organization_id' => $organization->id, 'minimum_nights' => $updateData];

        $responseData2 = $this->assertSuccessfulHttpApiRequest("/admin/device-minimum-nights", 'POST', $this->adminToken, $requestBody, true);
        usort($responseData2['data'], $idsorterFunc);
        $this->assertEquals($updateData, $responseData2['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_be_listed() {
        list(, $organization) = $this->prepare();

        $minNights = DeviceMinimumNights::whereIn('device_id', $organization->devices()->get()->pluck('id'))->get();
        $minNightsList = DeviceMinimumNightsEntity::getCollection($minNights);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/device-minimum-nights?organization_id=' . $organization->id, 'GET', $this->adminToken, [], true);

        $this->assertEquals($minNightsList, $responseData['data']);
    }

}
