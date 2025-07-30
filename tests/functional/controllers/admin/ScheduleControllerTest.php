<?php
namespace Tests\Functional\Controllers\Admin;

use App\Cruise;
use App\Entities\RelativeTimeEntity;
use App\Entities\ScheduleEntity;
use App\Facades\Config;
use App\RelativeTime;
use App\Schedule;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Tests\TestCase;

class ScheduleControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    public function it_can_list_schedules()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        $cruiseId = Cruise::first()->id;
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/schedule?cruise_id=' . $cruiseId, 'GET', $token);

        $this->assertCount(Schedule::forCruise($cruiseId)->count(), $responseData->data);
    }

    /**
     * @test
     */
    public function it_can_show_an_schedule()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        $scheduleId = Schedule::first()->id;
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/schedule/' . $scheduleId, 'GET', $token, [], true);

        $this->assertEquals((new ScheduleEntity(Schedule::find($scheduleId)))->getFrontendData([]), $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_store_an_schedule()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $cruise = Cruise::first();
        $weeklyFrequencyTxEntityData = (new TaxonomyEntity(Taxonomy::findOrFail(Config::getOrFail('taxonomies.schedule_frequencies.weekly'))))->getFrontendData(['translations']);
        $onceFrequencyTxEntityData = (new TaxonomyEntity(Taxonomy::findOrFail(Config::getOrFail('taxonomies.schedule_frequencies.once'))))->getFrontendData(['translations']);
        $relativeTimeEntityData = (new RelativeTimeEntity(RelativeTime::firstOrFail()))->getFrontendData(['time_of_day_taxonomy']);

        $data = [
            'cruise_id' => $cruise->id,
            'from_date' => '2026-03-24',
            'to_date' => '2026-04-16',
            'frequency' => $weeklyFrequencyTxEntityData,
            'relative_time' => $relativeTimeEntityData
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/schedule', 'POST', $token, $data, true);

        $this->assertTrue(!!$responseData['data']['id']);
        $data['id'] = $responseData['data']['id'];
        $this->assertEquals($data, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_update_an_schedule()
    {

        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $cruise = Cruise::first();
        $weeklyFrequencyTxEntityData = (new TaxonomyEntity(Taxonomy::findOrFail(Config::getOrFail('taxonomies.schedule_frequencies.weekly'))))->getFrontendData(['translations']);
        $onceFrequencyTxEntityData = (new TaxonomyEntity(Taxonomy::findOrFail(Config::getOrFail('taxonomies.schedule_frequencies.once'))))->getFrontendData(['translations']);
        $relativeTimeEntityData = (new RelativeTimeEntity(RelativeTime::firstOrFail()))->getFrontendData(['time_of_day_taxonomy']);


        $data = [
            'cruise_id' => $cruise->id,
            'from_date' => '2026-03-24',
            'to_date' => '2026-04-16',
            'frequency' => $weeklyFrequencyTxEntityData,
            'relative_time' => $relativeTimeEntityData
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/schedule', 'POST', $token, $data, true);

        $data['id'] = $responseData['data']['id'];
        $data['frequency'] = $onceFrequencyTxEntityData;

        $responseUpateData = $this->assertSuccessfulHttpApiRequest('/admin/schedule/'. $data['id'], 'PUT', $token, $data, true);

        $this->assertEquals((new ScheduleEntity(Schedule::find($responseData['data']['id'])))->getFrontendData([]), $responseUpateData['data']);
    }

    /**
     * @test
     */
    public function it_can_delete_an_schedule()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        $scheduleId = Schedule::first()->id;
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/schedule/' . $scheduleId, 'DELETE', $token, [], true);

        $this->assertEquals((new ScheduleEntity(Schedule::withTrashed()->findOrFail($scheduleId)))->getFrontendData([]), $responseData['data']);
    }
}
