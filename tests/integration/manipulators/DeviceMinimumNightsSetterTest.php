<?php

namespace Tests\Integration\Manipulators;

use App\DeviceMinimumNights;
use App\Exceptions\UserException;
use App\Manipulators\DeviceMinimumNightsSetter;
use App\Organization;
use Tests\TestCase;

class DeviceMinimumNightsSetterTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;

    private function prepare() {
        $org = Organization::findOrFail(5);
        return [$org->devices[0]->id, $org->dateRanges()->open()->first()->id];
    }

    /**
     * @test
     */
    function it_can_save_min_nights() {
        list($device_id, $date_range_id) = $this->prepare();
        $data = [
            'device_id' => $device_id,
            'date_range_id' => $date_range_id,
            'minimum_nights' => 10
        ];

        $minNights = (new DeviceMinimumNightsSetter($data))->set();
        $this->assertInstanceOf(DeviceMinimumNights::class, $minNights);

        $this->assertEquals($data['device_id'], $minNights->device_id);
        $this->assertEquals($data['date_range_id'], $minNights->date_range_id);
        $this->assertEquals($data['minimum_nights'], $minNights->minimum_nights);
    }

    /**
     * @test
     */
    function it_cant_save_min_nights_with_invalid_data() {
        list($device_id, $date_range_id) = $this->prepare();

        $this->expectException(UserException::class);

        $data = [
            'device_id' => 1,
            'date_range_id' => $date_range_id,
            'minimum_nights' => 10
        ];
        (new DeviceMinimumNightsSetter($data))->set();

        $data = [
            'device_id' => 999,
            'date_range_id' => $date_range_id,
            'minimum_nights' => 10
        ];
        (new DeviceMinimumNightsSetter($data))->set();
        $data = [
            'device_id' => $device_id,
            'date_range_id' => $date_range_id
        ];
        (new DeviceMinimumNightsSetter($data))->set();
    }

    /**
     * @test
     */
    function it_can_update_min_nights() {
        list($device_id, $date_range_id) = $this->prepare();
        
        $data = [
            'device_id' => $device_id,
            'date_range_id' => $date_range_id,
            'minimum_nights' => 10
        ];

        $minNights = (new DeviceMinimumNightsSetter($data))->set();
        $this->assertInstanceOf(DeviceMinimumNights::class, $minNights);


        $update = [
            'id' => $minNights->id,
            'device_id' => $device_id,
            'date_range_id' => $date_range_id,
            'minimum_nights' => 5
        ];

        $updatedDeviceMinimumNights = (new DeviceMinimumNightsSetter($update))->set();
        $this->assertInstanceOf(DeviceMinimumNights::class, $minNights);
        $this->assertEquals($minNights->id, $updatedDeviceMinimumNights->id);
        $this->assertEquals($update['minimum_nights'], $updatedDeviceMinimumNights->minimum_nights);
    }

}
