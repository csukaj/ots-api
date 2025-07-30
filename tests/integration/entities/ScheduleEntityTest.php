<?php
namespace Tests\Integration\Entities;

use App\Entities\RelativeTimeEntity;
use App\Entities\ScheduleEntity;
use App\Schedule;
use Tests\TestCase;

class ScheduleEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models_and_entity(): array
    {
        $Schedule = Schedule::first();
        return [$Schedule, (new ScheduleEntity($Schedule))];
    }

    /**
     * @test
     */
    function a_schedule_has_data()
    {
        list($schedule, $scheduleEntity) = $this->prepare_models_and_entity();

        $scheduleData = $scheduleEntity->getFrontendData();
        $this->assertEquals($schedule->cruise_id, $scheduleData['cruise_id']);
        $this->assertEquals($schedule->from_time, $scheduleData['from_date']);
        $this->assertEquals($schedule->to_time, $scheduleData['to_date']);
        $this->assertEquals($schedule->frequency->name, $scheduleData['frequency']['name']);
        $this->assertEquals((new RelativeTimeEntity($schedule->relativeTime))->getFrontendData(['time_of_day_taxonomy']), $scheduleData['relative_time']);

        ;
    }
}
