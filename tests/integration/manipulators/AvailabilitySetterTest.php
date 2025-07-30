<?php

namespace Tests\Integration\Manipulators;

use App\Availability;
use App\Device;
use App\Exceptions\UserException;
use App\Manipulators\AvailabilitySetter;
use App\Organization;
use Tests\TestCase;

class AvailabilitySetterTest extends TestCase
{
    static $defaultAmount = 20;

    protected function prepareDevice($amount = null)
    {
        return factory(Device::class, 'room')->create([
            'amount' => !is_null($amount) ? $amount : self::$defaultAmount
        ]);
    }

    private function assertAvailabilityEquals($expectedArray, $actualObject)
    {
        $this->assertTrue(!!$actualObject->id);
        $this->assertEquals($expectedArray['availableType'], $actualObject->available_type);
        $this->assertEquals($expectedArray['availableId'], $actualObject->available_id);
        $this->assertEquals($expectedArray['fromDate'], $actualObject->from_time);
        if (is_null($expectedArray['toDate'])) {
            $this->assertNull($actualObject->to_time);
        } else {
            $this->assertEquals($expectedArray['toDate'], $actualObject->to_time);
        }
        $this->assertEquals($expectedArray['amount'], $actualObject->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_be_set_as_infinite_interval()
    {
        $device = $this->prepareDevice();
        $intervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-22',
            'toDate' => null,
            'amount' => 10
        ];
        $availability = (new AvailabilitySetter($intervalData))->set();

        $this->assertAvailabilityEquals($intervalData, $availability);

    }

    /**
     * @test
     * @throws \Exception
     * @throws \Throwable
     */
    function it_can_be_set_as_normal_interval()
    {
        $device = $this->prepareDevice();

        $intervals = [
            [
                'availableType' => Device::class,
                'availableId' => $device->id,
                'fromDate' => '2026-01-02',
                'toDate' => '2026-01-03',
                'amount' => 2
            ],
            [
                'availableType' => Device::class,
                'availableId' => $device->id,
                'fromDate' => '2026-01-03',
                'toDate' => '2026-01-05',
                'amount' => 5
            ]
        ];


        (new AvailabilitySetter($intervals[0]))->set();
        $availability = (new AvailabilitySetter($intervals[1]))->set();

        $this->assertAvailabilityEquals($intervals[1], $availability);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_detect_an_identical_overlap()
    {
        $device = $this->prepareDevice();
        $availability1 = (new AvailabilitySetter([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-22',
            'toDate' => '2026-06-23',
            'amount' => 10
        ]))->set();
        $intervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-22',
            'toDate' => '2026-06-23',
            'amount' => 8
        ];
        $availability2 = (new AvailabilitySetter($intervalData))->set();

        $this->assertEquals($availability1->id, $availability2->id);
        $this->assertAvailabilityEquals($intervalData, $availability2);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_be_set_into_an_infinite_interval_with_same_start_date()
    {
        $device = $this->prepareDevice();

        $intervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => date('Y-m-d'),
            'toDate' => '2026-06-24',
            'amount' => 8
        ];
        (new AvailabilitySetter($intervalData))->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(2, count($availabilities));

        $this->assertAvailabilityEquals($intervalData, $availabilities[0]);

        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-24',
            'toDate' => null,
            'amount' => self::$defaultAmount
        ], $availabilities[1]);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_be_set_into_an_infinite_interval_with_any_start_date()
    {
        $device = $this->prepareDevice();

        $normalIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 8
        ];

        (new AvailabilitySetter($normalIntervalData))->set();

        $availabilities = Availability::getAll(Device::class, $device->id);

        $this->assertEquals(3, count($availabilities));

        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => date('Y-m-d'),
            'toDate' => '2026-06-30',
            'amount' => self::$defaultAmount
        ], $availabilities[0]);
        $this->assertAvailabilityEquals($normalIntervalData, $availabilities[1]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => null,
            'amount' => self::$defaultAmount
        ], $availabilities[2]);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_create_many_interval_infinite_interval_still_exists()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];
        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(3, count($availabilities));

        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => date('Y-m-d'),
            'toDate' => '2026-06-27',
            'amount' => self::$defaultAmount
        ], $availabilities[0]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ], $availabilities[1]);

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];
        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => date('Y-m-d'),
            'toDate' => '2026-06-27',
            'amount' => self::$defaultAmount
        ], $availabilities[0]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ], $availabilities[1]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ], $availabilities[2]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => null,
            'amount' => self::$defaultAmount
        ], $availabilities[3]);

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => '2026-07-15',
            'amount' => 8
        ];
        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(5, count($availabilities));

        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => date('Y-m-d'),
            'toDate' => '2026-06-27',
            'amount' => self::$defaultAmount
        ], $availabilities[0]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ], $availabilities[1]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ], $availabilities[2]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => '2026-07-15',
            'amount' => 8
        ], $availabilities[3]);
        $this->assertAvailabilityEquals([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-15',
            'toDate' => null,
            'amount' => self::$defaultAmount
        ], $availabilities[4]);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_create_new_interval_between_two_normal_interval()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-28',
            'toDate' => '2026-07-02',
            'amount' => 8
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(5, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-06-27', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-06-27', $availabilities[1]->from_time);
        $this->assertEquals('2026-06-28', $availabilities[1]->to_time);
        $this->assertEquals(10, $availabilities[1]->amount);

        $this->assertEquals('2026-06-28', $availabilities[2]->from_time);
        $this->assertEquals('2026-07-02', $availabilities[2]->to_time);
        $this->assertEquals(8, $availabilities[2]->amount);

        $this->assertEquals('2026-07-02', $availabilities[3]->from_time);
        $this->assertEquals('2026-07-05', $availabilities[3]->to_time);
        $this->assertEquals(9, $availabilities[3]->amount);

        $this->assertEquals('2026-07-05', $availabilities[4]->from_time);
        $this->assertNull($availabilities[4]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[4]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_lengthen_an_interval_by_damage_an_other()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => '2026-07-15',
            'amount' => 8
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();


        $fourthIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-10',
            'amount' => 7
        ];

        $fourthInterVal = new AvailabilitySetter($fourthIntervalData);
        $fourthInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(5, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-06-27', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-06-27', $availabilities[1]->from_time);
        $this->assertEquals('2026-06-30', $availabilities[1]->to_time);
        $this->assertEquals(10, $availabilities[1]->amount);

        $this->assertEquals('2026-06-30', $availabilities[2]->from_time);
        $this->assertEquals('2026-07-10', $availabilities[2]->to_time);
        $this->assertEquals(7, $availabilities[2]->amount);

        $this->assertEquals('2026-07-10', $availabilities[3]->from_time);
        $this->assertEquals('2026-07-15', $availabilities[3]->to_time);
        $this->assertEquals(8, $availabilities[3]->amount);

        $this->assertEquals('2026-07-15', $availabilities[4]->from_time);
        $this->assertNull($availabilities[4]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[4]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_merge_intervals_if_start_and_end_times_are_equals()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => '2026-07-15',
            'amount' => 8
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $fourthIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-15',
            'amount' => 7
        ];

        $fourthInterVal = new AvailabilitySetter($fourthIntervalData);
        $fourthInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-06-27', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-06-27', $availabilities[1]->from_time);
        $this->assertEquals('2026-06-30', $availabilities[1]->to_time);
        $this->assertEquals(10, $availabilities[1]->amount);

        $this->assertEquals('2026-06-30', $availabilities[2]->from_time);
        $this->assertEquals('2026-07-15', $availabilities[2]->to_time);
        $this->assertEquals(7, $availabilities[2]->amount);

        $this->assertEquals('2026-07-15', $availabilities[3]->from_time);
        $this->assertNull($availabilities[3]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[3]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_lengthen_an_interval_by_damage_infinite_interval()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-10',
            'amount' => 7
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-06-27', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-06-27', $availabilities[1]->from_time);
        $this->assertEquals('2026-06-30', $availabilities[1]->to_time);
        $this->assertEquals(10, $availabilities[1]->amount);

        $this->assertEquals('2026-06-30', $availabilities[2]->from_time);
        $this->assertEquals('2026-07-10', $availabilities[2]->to_time);
        $this->assertEquals(7, $availabilities[2]->amount);

        $this->assertEquals('2026-07-10', $availabilities[3]->from_time);
        $this->assertNull($availabilities[3]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[3]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_create_new_interval_between_normal_interval_and_infinite_interval()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-01',
            'toDate' => '2026-07-10',
            'amount' => 8
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(5, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-06-27', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-06-27', $availabilities[1]->from_time);
        $this->assertEquals('2026-06-30', $availabilities[1]->to_time);
        $this->assertEquals(10, $availabilities[1]->amount);

        $this->assertEquals('2026-06-30', $availabilities[2]->from_time);
        $this->assertEquals('2026-07-01', $availabilities[2]->to_time);
        $this->assertEquals(9, $availabilities[2]->amount);

        $this->assertEquals('2026-07-01', $availabilities[3]->from_time);
        $this->assertEquals('2026-07-10', $availabilities[3]->to_time);
        $this->assertEquals(8, $availabilities[3]->amount);

        $this->assertEquals('2026-07-10', $availabilities[4]->from_time);
        $this->assertNull($availabilities[4]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[4]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_lengthen_an_interval_by_damage_infinite_interval_and_delete_intervals_in_between()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => '2026-07-10',
            'amount' => 7
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $fourthIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-12',
            'amount' => 6
        ];

        $fourthInterVal = new AvailabilitySetter($fourthIntervalData);
        $fourthInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-06-27', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-06-27', $availabilities[1]->from_time);
        $this->assertEquals('2026-06-30', $availabilities[1]->to_time);
        $this->assertEquals(10, $availabilities[1]->amount);

        $this->assertEquals('2026-06-30', $availabilities[2]->from_time);
        $this->assertEquals('2026-07-12', $availabilities[2]->to_time);
        $this->assertEquals(6, $availabilities[2]->amount);

        $this->assertEquals('2026-07-12', $availabilities[3]->from_time);
        $this->assertNull($availabilities[3]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[3]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_create_new_interval_between_normal_interval_and_infinite_interval_and_delete_intervals_in_between()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => '2026-07-10',
            'amount' => 7
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $fourthIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-29',
            'toDate' => '2026-07-12',
            'amount' => 6
        ];

        $fourthInterVal = new AvailabilitySetter($fourthIntervalData);
        $fourthInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-06-27', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-06-27', $availabilities[1]->from_time);
        $this->assertEquals('2026-06-29', $availabilities[1]->to_time);
        $this->assertEquals(10, $availabilities[1]->amount);

        $this->assertEquals('2026-06-29', $availabilities[2]->from_time);
        $this->assertEquals('2026-07-12', $availabilities[2]->to_time);
        $this->assertEquals(6, $availabilities[2]->amount);

        $this->assertEquals('2026-07-12', $availabilities[3]->from_time);
        $this->assertNull($availabilities[3]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[3]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_merge_intervals_if_start_and_end_times_are_equals_and_delete_intervals_in_between()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-27',
            'toDate' => '2026-06-30',
            'amount' => 10
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-05',
            'amount' => 9
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-05',
            'toDate' => '2026-07-10',
            'amount' => 7
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $fourthIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-07-10',
            'toDate' => '2026-07-12',
            'amount' => 6
        ];

        $fourthInterVal = new AvailabilitySetter($fourthIntervalData);
        $fourthInterVal->set();

        $fifthIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-06-30',
            'toDate' => '2026-07-12',
            'amount' => 4
        ];

        $fifthInterVal = new AvailabilitySetter($fifthIntervalData);
        $fifthInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-06-27', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-06-27', $availabilities[1]->from_time);
        $this->assertEquals('2026-06-30', $availabilities[1]->to_time);
        $this->assertEquals(10, $availabilities[1]->amount);

        $this->assertEquals('2026-06-30', $availabilities[2]->from_time);
        $this->assertEquals('2026-07-12', $availabilities[2]->to_time);
        $this->assertEquals(4, $availabilities[2]->amount);

        $this->assertEquals('2026-07-12', $availabilities[3]->from_time);
        $this->assertNull($availabilities[3]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[3]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_overwrite_an_automatically_set_infinite_interval()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => self::$defaultAmount
        ];
        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => 2
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(3, count($availabilities));

        $this->assertEquals(date('Y-m-d'), $availabilities[0]->from_time);
        $this->assertEquals('2026-01-02', $availabilities[0]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[0]->amount);

        $this->assertEquals('2026-01-02', $availabilities[1]->from_time);
        $this->assertEquals('2026-01-03', $availabilities[1]->to_time);
        $this->assertEquals(2, $availabilities[1]->amount);

        $this->assertEquals('2026-01-03', $availabilities[2]->from_time);
        $this->assertEquals(null, $availabilities[2]->to_time);
        $this->assertEquals(self::$defaultAmount, $availabilities[2]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_merge_intervals_by_amount_with_before_datepoint_interval()
    {
        $device = $this->prepareDevice();

        $zeroIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-01',
            'toDate' => '2026-01-02',
            'amount' => 1
        ];

        $zeroInterVal = new AvailabilitySetter($zeroIntervalData);
        $zeroInterVal->set();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => 2
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-03',
            'toDate' => '2026-01-05',
            'amount' => 2
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertEquals('2026-01-02', $availabilities[2]->from_time);
        $this->assertEquals('2026-01-05', $availabilities[2]->to_time);
        $this->assertEquals(2, $availabilities[2]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_merge_intervals_by_amount_with_after_datepoint_interval()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => 1
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-03',
            'toDate' => '2026-01-05',
            'amount' => 2
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => 2
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(3, count($availabilities));

        $this->assertEquals('2026-01-02', $availabilities[1]->from_time);
        $this->assertEquals('2026-01-05', $availabilities[1]->to_time);
        $this->assertEquals(2, $availabilities[1]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_merge_intervals_by_amount_with_before_and_after_datepoint_interval_both()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => 2
        ];

        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-03',
            'toDate' => '2026-01-05',
            'amount' => 3
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-05',
            'toDate' => '2026-01-08',
            'amount' => 2
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $forthIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-08',
            'toDate' => '2026-01-10',
            'amount' => 5
        ];

        $forthInterVal = new AvailabilitySetter($forthIntervalData);
        $forthInterVal->set();

        $fifthIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-03',
            'toDate' => '2026-01-05',
            'amount' => 2
        ];

        $fifthInterVal = new AvailabilitySetter($fifthIntervalData);
        $fifthInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertEquals('2026-01-02', $availabilities[1]->from_time);
        $this->assertEquals('2026-01-08', $availabilities[1]->to_time);
        $this->assertEquals(2, $availabilities[1]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_merge_intervals_by_amount_with_after_infinite_interval()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => 2
        ];
        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => 5
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-03',
            'toDate' => '2026-01-05',
            'amount' => 2
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(4, count($availabilities));

        $this->assertEquals('2026-01-03', $availabilities[2]->from_time);
        $this->assertEquals('2026-01-05', $availabilities[2]->to_time);
        $this->assertEquals(2, $availabilities[2]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_merge_intervals_by_amount_with_before_and_after_infinite_interval()
    {
        $device = $this->prepareDevice();

        $firstIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-02',
            'toDate' => '2026-01-03',
            'amount' => 2
        ];
        $firstInterVal = new AvailabilitySetter($firstIntervalData);
        $firstInterVal->set();

        $secondIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-03',
            'toDate' => '2026-01-05',
            'amount' => 5
        ];

        $secondInterVal = new AvailabilitySetter($secondIntervalData);
        $secondInterVal->set();

        $thirdIntervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-03',
            'toDate' => '2026-01-05',
            'amount' => 2
        ];

        $thirdInterVal = new AvailabilitySetter($thirdIntervalData);
        $thirdInterVal->set();

        $availabilities = Availability::getAll(Device::class, $device->id);
        $this->assertEquals(3, count($availabilities));

        $this->assertEquals('2026-01-02', $availabilities[1]->from_time);
        $this->assertEquals('2026-01-05', $availabilities[1]->to_time);
        $this->assertEquals(2, $availabilities[1]->amount);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_be_set_when_interval_is_inside_a_single_interval()
    {
        $org = Organization::findOrFail(1);
        $device = $org->devices[0];

        $setter = new AvailabilitySetter([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-01',
            'toDate' => '2026-02-13',
            'amount' => self::$defaultAmount
        ]);
        $availability = $setter->set();
        $this->assertInstanceOf(Availability::class,$availability);

        $setter = new AvailabilitySetter([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-02-13',
            'toDate' => '2026-06-01',
            'amount' => 2
        ]);
        $availability = $setter->set();
        $this->assertInstanceOf(Availability::class,$availability);
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_be_set_and_get_when_interval_is_inside_a_single_interval()
    {
        $org = Organization::findOrFail(1);
        $device = $org->devices[0];

        $availabilitiesCount = count($device->availabilities()->get());

        $setter = new AvailabilitySetter([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-01',
            'toDate' => '2026-02-10',
            'amount' => 0
        ]);
        $setter->set();

        $this->assertEquals($availabilitiesCount + 2, count($device->availabilities()->get()));
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_be_set_and_get_when_interval_is_partly_identical()
    {
        $org = Organization::findOrFail(1);
        $device = $org->devices[0];

        $availabilitiesCount = count($device->availabilities()->get());

        $setter = new AvailabilitySetter([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-01',
            'toDate' => '2026-02-10',
            'amount' => 0
        ]);
        $setter->set();

        $setter = new AvailabilitySetter([
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-01-01',
            'toDate' => '2026-01-03',
            'amount' => 2
        ]);
        $setter->set();

        $this->assertEquals($availabilitiesCount + 2, count($device->availabilities()->get()));
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_not_set_an_interval_when_from_date_is_later_than_to_date()
    {
        $this->expectException(UserException::class);
        $this->expectExceptionMessageRegExp('/fromDate is later than toDate/');

        $device = $this->prepareDevice();
        $intervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => '2026-08-11',
            'toDate' => '2012-08-11',
            'amount' => 10
        ];
        (new AvailabilitySetter($intervalData))->set();
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_be_modified()
    {
        $amount = 10;
        $fromDate = '2026-06-22';
        $modification = -2;
        $device = $this->prepareDevice($amount);

        $intervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => $fromDate,
            'toDate' => null
        ];
        $modified = (new AvailabilitySetter($intervalData))->modify($modification);
        $this->assertTrue($modified);
        $availabilitiesAfter = Availability::forAvailable(Device::class, $device->id)->get();
        $this->assertCount(2, $availabilitiesAfter);
        $expectedFirst = [
            'available_type' => Device::class,
            'available_id' => $device->id,
            'from_time' => date('Y-m-d'),
            'to_time' => $fromDate,
            'amount' => $amount
        ];
        $this->assertArraySubset($expectedFirst, $availabilitiesAfter->first()->toArray());
        $expectedLast = [
            'available_type' => Device::class,
            'available_id' => $device->id,
            'from_time' => $fromDate,
            'to_time' => null,
            'amount' => $amount + $modification
        ];
        $this->assertArraySubset($expectedLast, $availabilitiesAfter->last()->toArray());
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_cant_be_modified_outside_range_of_zero_and_device_amount()
    {
        $amount = 10;
        $fromDate = '2026-06-22';
        $modification = 20;
        $device = $this->prepareDevice($amount);

        $intervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => $fromDate,
            'toDate' => null
        ];

        $modified = (new AvailabilitySetter($intervalData))->modify($modification);
        $this->assertTrue($modified);
        $availabilitiesAfter = Availability::forAvailable(Device::class, $device->id)->get();
        $this->assertCount(1, $availabilitiesAfter);

        $expected = [
            'available_type' => Device::class,
            'available_id' => $device->id,
            'from_time' => date('Y-m-d'),
            'to_time' => null,
            'amount' => max(min($amount + $modification, $amount), 0)
        ];
        $this->assertArraySubset($expected, $availabilitiesAfter->first()->toArray());

        $device = $this->prepareDevice($amount);
        $modification = -20;
        $intervalData = [
            'availableType' => Device::class,
            'availableId' => $device->id,
            'fromDate' => $fromDate,
            'toDate' => null
        ];

        $modified = (new AvailabilitySetter($intervalData))->modify($modification);
        $this->assertTrue($modified);
        $availabilitiesAfter = Availability::forAvailable(Device::class, $device->id)->get();
        $this->assertCount(2, $availabilitiesAfter);

        $expectedFirst = [
            'available_type' => Device::class,
            'available_id' => $device->id,
            'from_time' => date('Y-m-d'),
            'to_time' => $fromDate,
            'amount' => $amount
        ];
        $this->assertArraySubset($expectedFirst, $availabilitiesAfter->first()->toArray());
        $expectedLast = [
            'available_type' => Device::class,
            'available_id' => $device->id,
            'from_time' => $fromDate,
            'to_time' => null,
            'amount' => max(min($amount + $modification, $amount), 0)
        ];
        $this->assertArraySubset($expectedLast, $availabilitiesAfter->last()->toArray());
    }

}
