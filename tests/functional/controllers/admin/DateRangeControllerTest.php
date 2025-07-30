<?php

namespace Tests\Functional\Controllers\Admin;

use App\DateRange;
use App\Organization;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class DateRangeControllerTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_date_ranges() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $dateRanges = Organization::findOrFail(1)->dateRanges->sortBy('from_time')->values();
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/date-range?date_rangeable_type=App\Organization&date_rangeable_id=1', 'GET', $token);

        $this->assertEquals(count($dateRanges), count($responseData->data));

        foreach ($dateRanges as $dateRange) {
            
            $found = false;
            foreach($responseData->data as $rD){
                if($rD->id == $dateRange->id){
                    $dateRangeData = $rD;  
                    $found = true;
                }
            }

            $this->assertTrue($found);
            if ($dateRange->name) {
                $this->assertEquals((new DescriptionEntity($dateRange->name))->getFrontendData(), (array)$dateRangeData->name);
            }
            $this->assertEquals($dateRange->from_time, $dateRangeData->from_date);
            $this->assertEquals($dateRange->to_time, $dateRangeData->to_date);
            $this->assertEquals($dateRange->type->name, $dateRangeData->type);
            if ('closed' != $dateRange->type->name) {
                $this->assertEquals($dateRange->marginType->name, $dateRangeData->margin_type);
            }
            $this->assertEquals($dateRange->margin_value, $dateRangeData->margin_value);
            $this->assertEquals(count($dateRange->modelMealPlans), count($dateRangeData->meal_plans));
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_open_and_closed_date_ranges() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $dateRanges = Organization::findOrFail(1)->dateRanges;
        $openResponseData = $this->assertSuccessfulHttpApiRequest('/admin/date-range?date_rangeable_type=App\Organization&date_rangeable_id=1&type=open', 'GET', $token);
        $closedResponseData = $this->assertSuccessfulHttpApiRequest('/admin/date-range?date_rangeable_type=App\Organization&date_rangeable_id=1&type=closed', 'GET', $token);

        $openCount = 0;
        $closedCount = 0;
        foreach ($dateRanges as $dateRange) {
            if ($dateRange->type->name == 'open') {
                $openCount++;
            } elseif ($dateRange->type->name == 'closed') {
                $closedCount++;
            }
        }
        $this->assertEquals($openCount, count($openResponseData->data));
        $this->assertEquals($closedCount, count($closedResponseData->data));
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_show_a_date_range() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $dateRange = Organization::findOrFail(1)->dateRanges[0];
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/date-range/{$dateRange->id}", 'GET', $token);

        $this->assertEquals($dateRange->id, $responseData->data->id);
        $this->assertEquals((new DescriptionEntity($dateRange->name))->getFrontendData(), (array)$responseData->data->name);
        $this->assertEquals($dateRange->from_time, $responseData->data->from_date);
        $this->assertEquals($dateRange->to_time, $responseData->data->to_date);
        $this->assertEquals($dateRange->type->name, $responseData->data->type);
        $this->assertEquals($dateRange->marginType->name, $responseData->data->margin_type);
        $this->assertEquals($dateRange->margin_value, $responseData->data->margin_value);
        $this->assertEquals(count($dateRange->modelMealPlans), count($responseData->data->meal_plans));
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_a_date_range() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $nameDescription = $this->faker->word;

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/date-range', 'POST', $token, [
            'name' => ['en' => $nameDescription],
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => 1,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-10',
            'type' => 'open',
            'margin_value' => 11,
            'meal_plans' => ['f/b', 'h/b', 'inc']
        ]);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($nameDescription, $responseData->data->name->en);
        $this->assertEquals('2026-01-02', $responseData->data->from_date);
        $this->assertEquals('2026-01-10', $responseData->data->to_date);
        $this->assertEquals('open', $responseData->data->type);
        $this->assertEquals('percentage', $responseData->data->margin_type);
        $this->assertEquals(11, $responseData->data->margin_value);
        $this->assertEqualArrayContents(['f/b', 'h/b', 'inc'], $responseData->data->meal_plans);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_delete_a_date_range() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $deviceUsageCountBefore = DateRange::all()->count();

        $responseDataCreate = $this->assertSuccessfulHttpApiRequest('/admin/date-range', 'POST', $token, [
            'name' => ['en' => $this->faker->word],
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => 1,
            'from_date' => '2026-01-11',
            'to_date' => '2026-01-20',
            'type' => 'open',
            'margin_value' => 11,
            'meal_plans' => ['f/b', 'h/b', 'inc']
        ]);

        $responseDataDel = $this->assertSuccessfulHttpApiRequest("/admin/date-range/{$responseDataCreate->data->id}", 'DELETE', $token);
        $this->assertEquals($responseDataCreate->data->id, $responseDataDel->data->id);
        $this->assertEquals($deviceUsageCountBefore, DateRange::all()->count());
    }

}
