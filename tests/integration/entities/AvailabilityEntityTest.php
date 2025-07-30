<?php

namespace Tests\Integration\Entities;

use App\Device;
use App\Entities\AvailabilityEntity;
use App\Manipulators\AvailabilitySetter;
use App\Organization;
use Tests\TestCase;

class AvailabilityEntityTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    function it_can_be_set_at_the_beginning_of_the_year()
    {
        $org = Organization::findOrFail(2);
        $device = $org->devices[0];
        $availabilityData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2028-01-01',
            'toDate' => '2028-01-03',
            'amount' => 1
        ];
        (new AvailabilitySetter($availabilityData))->set();

        $this->assertEquals(
            $availabilityData['amount'],
            (new AvailabilityEntity($availabilityData['availableType'], $availabilityData['availableId']))
                ->get($availabilityData['fromDate'], '2028-01-05')[0]['amount']);
    }
}