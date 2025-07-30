<?php

namespace Tests\Integration\Models;

use App\Accommodation;
use App\Availability;
use App\Device;
use App\Manipulators\DeviceSetter;
use App\Organization;
use Tests\TestCase;

class DeviceTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @param null $accommodationId
     * @param int $index
     * @return Device
     * @throws \Exception
     */
    private function prepare_a_device_model($accommodationId = null, $index = null): Device
    {
        if ($accommodationId) {
            $accommodation = Accommodation::findOrFail($accommodationId);
        } else {
            $accommodation = factory(Organization::class, 'accommodation')->create();

        }

        if (!is_null($index)) {
            return $accommodation->devices[$index];
        }
        $data = [
            'deviceable_id' => $accommodation->id,
            'deviceable_type' => Organization::class,
            'type' => 'room',
            'amount' => 2,
            'name' => [
                'en' => 'Room Test Create1'
            ]
        ];

        return (new DeviceSetter($data))->set();
    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_check_if_name_exists()
    {
        $accommodationId = 1;
        $device = $this->prepare_a_device_model($accommodationId);
        $this->assertTrue(Device::deviceNameExists($accommodationId, Organization::class, [$device->name->name]));
        $this->assertFalse(Device::deviceNameExists($accommodationId, Organization::class, [$this->faker->sentence]));

    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_getDevicesChannelManagerId()
    {
        $expectedForHLSTestHotel = [
            31 => '2b812012-1179-1541584392-4b42-90cd-f9b36d8445d2',
            32 => 'cf97f62e-ad49-1541584582-4892-9248-c894d3a1fe71',
            33 => '6a7da937-b691-1507169291-4916-8db0-0af3cc8bd199',
            34 => '6e1c1409-0c83-1507192559-42b1-b85a-b0a63ab6b14d',
            35 => '3a138aae-ac2b-1507002078-4a25-a346-e795cbe9eea4',
            36 => '3437b4e1-36d9-1524733448-4f85-bd61-641394ebd6dd',
            37 => '4301edc5-45f9-1522288287-446b-b7dc-9751bc8f5352',
            38 => 'fb332fbe-8a3f-1524732941-4927-8b96-f94ec114a95a',
            39 => '25c57ba9-9120-1524733567-4cb6-8780-483b66db7bf6',
            40 => '2a3f2a4b-62a4-1524732209-4cc3-b58a-0216aa81875c',
            41 => 'e103fa99-9274-1524733072-4ff5-a6b3-000fe1e484e6'
        ];
        $this->assertEquals($expectedForHLSTestHotel, Device::getDevicesChannelManagerId(21));
        $device = $this->prepare_a_device_model();
        $this->assertEquals([], Device::getDevicesChannelManagerId($device->deviceable_id));

    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_set_default_availability_on_creation()
    {
        $device = factory(Device::class, 'room')->create();
        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertCount(1, $availabilities);
        $this->assertArraySubset([
            "available_id" => $device->id,
            "from_time" => date('Y-m-d'),
            "to_time" => null,
            "amount" => $device->amount,
            "available_type" => Device::class
        ], $availabilities->first()->toArray());
    }

    /**
     * @test
     * @throws \Exception
     */
    function availability_is_modified_on_device_update()
    {
        $startAmount = 10;
        $device = factory(Device::class, 'room')->create(['amount' => $startAmount]);
        $this->assertEquals($startAmount, Availability::getAll(Device::class, $device->id)->first()->amount);

        $modifiedAmount = 17;
        $device->amount = $modifiedAmount;
        $device->saveOrFail();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertCount(1, $availabilities);
        $this->assertArraySubset([
            "available_id" => $device->id,
            "from_time" => date('Y-m-d'),
            "to_time" => null,
            "amount" => $modifiedAmount,
            "available_type" => Device::class
        ], $availabilities->first()->toArray());
    }
}
