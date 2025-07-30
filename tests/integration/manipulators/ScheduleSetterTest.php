<?php
namespace Tests\Integration\Manipulators;

use App\Cruise;
use App\Entities\RelativeTimeEntity;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Manipulators\ScheduleSetter;
use App\RelativeTime;
use App\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;
use function factory;

class ScheduleSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare(): array
    {
        $cruise = Cruise::first();
        $schedule = factory(Schedule::class)->create([
            'cruise_id' => $cruise->id,
            'from_time' => '2026-01-23',
            'to_time' => '2026-04-01',
        ]);

        $weeklyFrequencyTxEntityData = (new TaxonomyEntity(Taxonomy::findOrFail(Config::getOrFail('taxonomies.schedule_frequencies.weekly'))))->getFrontendData();
        $onceFrequencyTxEntityData = (new TaxonomyEntity(Taxonomy::findOrFail(Config::getOrFail('taxonomies.schedule_frequencies.once'))))->getFrontendData();
        $relativeTimeEntityData = (new RelativeTimeEntity(RelativeTime::firstOrFail()))->getFrontendData();

        return [$schedule, $cruise, $weeklyFrequencyTxEntityData, $onceFrequencyTxEntityData, $relativeTimeEntityData];
    }

    /**
     * @test
     */
    public function it_can_set_a_schedule()
    {
        list(, $cruise, $weeklyFrequencyTxEntityData, $onceFrequencyTxEntityData, $relativeTimeEntityData) = $this->prepare();
        $data = [
            'cruise_id' => $cruise->id,
            'from_date' => '2026-03-24',
            'to_date' => '2026-04-16',
            'frequency' => $weeklyFrequencyTxEntityData,
            'relative_time' => $relativeTimeEntityData
        ];

        $schedule = (new ScheduleSetter($data))->set();

        $this->assertTrue(!!$schedule->id);
        $this->assertInstanceOf(Schedule::class, $schedule);
        $this->assertEquals($data['cruise_id'], $schedule->cruise_id);
        $this->assertEquals($data['from_date'], $schedule->from_time);
        $this->assertEquals($data['to_date'], $schedule->to_time);
        $this->assertEquals($data['frequency']['id'], $schedule->frequency_taxonomy_id);
        $this->assertEquals($data['relative_time']['id'], $schedule->relative_time_id);
    }

    /**
     * @test
     */
    function it_can_modified()
    {
        list(, $cruise, $weeklyFrequencyTxEntityData, $onceFrequencyTxEntityData, $relativeTimeEntityData) = $this->prepare();
        $data = [
            'cruise_id' => $cruise->id,
            'from_date' => '2026-03-24',
            'to_date' => '2026-04-16',
            'frequency' => $weeklyFrequencyTxEntityData,
            'relative_time' => $relativeTimeEntityData
        ];

        $schedule = (new ScheduleSetter($data))->set();

        $this->assertTrue(!!$schedule->id);

        $data['id'] = $schedule->id;
        $data['frequency'] = $onceFrequencyTxEntityData;

        $scheduleUpdated = (new ScheduleSetter($data))->set();
        $this->assertEquals($schedule->id, $scheduleUpdated->id);
        $this->assertEquals($data['frequency']['name'], $scheduleUpdated->frequency->name);
    }

    /**
     * @test
     */
    function it_can_restored()
    {
        list(, $cruise, $weeklyFrequencyTxEntityData, $onceFrequencyTxEntityData, $relativeTimeEntityData) = $this->prepare();
        $data = [
            'cruise_id' => $cruise->id,
            'from_date' => '2026-03-24',
            'to_date' => '2026-04-16',
            'frequency' => $weeklyFrequencyTxEntityData,
            'relative_time' => $relativeTimeEntityData
        ];
        $schedule = (new ScheduleSetter($data))->set();

        $this->assertTrue(!!$schedule->id);

        $this->assertTrue((bool) Schedule::destroy($schedule->id));

        $scheduleRestored = (new ScheduleSetter($data))->set();
        $this->assertEquals($schedule->id, $scheduleRestored->id);
        $this->assertEquals($data['from_date'], $scheduleRestored->from_time);
    }

    /**
     * @test
     */
    function it_cannot_set_schedule_with_bad_input_data()
    {
        list(, $cruise, $weeklyFrequencyTxEntityData, $onceFrequencyTxEntityData, $relativeTimeEntityData) = $this->prepare();

        $this->expectException(ModelNotFoundException::class);

        $this->expectExceptionMessageRegExp('/No query results for model/');
        (new ScheduleSetter([
        'cruise_id' => 9999,
        'from_date' => '2026-03-24',
        'to_date' => '2026-04-16',
        'frequency' => $weeklyFrequencyTxEntityData,
        'relative_time' => $relativeTimeEntityData
        ]))->set();

        $this->expectException(UserException::class);
        $this->expectExceptionMessageRegExp('/Valid Cruise id is required!/');
        (new ScheduleSetter([
        'cruise_id' => null,
        'from_date' => '2026-03-24',
        'to_date' => '2026-04-16',
        'frequency' => $weeklyFrequencyTxEntityData,
        'relative_time' => $relativeTimeEntityData
        ]))->set();

        $this->expectExceptionMessageRegExp('/From date is required!/');
        (new ScheduleSetter([
        'cruise_id' => $cruise->id,
        'from_date' => null,
        'to_date' => '2026-04-16',
        'frequency' => $weeklyFrequencyTxEntityData,
        'relative_time' => $relativeTimeEntityData
        ]))->set();

        $this->expectExceptionMessageRegExp('/Invalid date range: wrong date order!/');
        (new ScheduleSetter([
        'cruise_id' => $cruise->id,
        'from_date' => '2026-04-16',
        'to_date' => '2026-03-24',
        'frequency' => $weeklyFrequencyTxEntityData,
        'relative_time' => $relativeTimeEntityData
        ]))->set();

        $this->expectExceptionMessageRegExp('/Valid frequency is required!/');
        (new ScheduleSetter([
        'cruise_id' => $cruise->id,
        'from_date' => '2026-03-24',
        'to_date' => '2026-04-16',
        'frequency' => null,
        'relative_time' => $relativeTimeEntityData
        ]))->set();

        $this->expectExceptionMessageRegExp('/Valid frequency is required!/');
        (new ScheduleSetter([
        'cruise_id' => $cruise->id,
        'from_date' => '2026-03-24',
        'to_date' => '2026-04-16',
        'frequency' => 123344,
        'relative_time' => $relativeTimeEntityData
        ]))->set();

        $this->expectExceptionMessageRegExp('/Relative time is required!/');
        (new ScheduleSetter([
        'cruise_id' => $cruise->id,
        'from_date' => '2026-03-24',
        'to_date' => '2026-04-16',
        'frequency' => $weeklyFrequencyTxEntityData,
        'relative_time' => null
        ]))->set();
    }
}
