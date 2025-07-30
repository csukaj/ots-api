<?php
namespace Tests\Integration\Entities;

use App\DateRange;
use App\Entities\OrganizationEntity;
use App\Organization;
use Tests\TestCase;

class OrganizationEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity(): array
    {
        $organization = Organization::findOrFail(1);
        return [$organization, (new OrganizationEntity($organization))];
    }

    /**
     * @test
     */
    function an_accommodation_has_basic_data()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData();

        $this->assertEquals($organization->id, $frontendData['id']);
        $this->assertTrue(!empty($frontendData['name']['en']));
        $this->assertEquals($organization->type->name, $frontendData['type']);
        $this->assertEquals($organization->is_active, $frontendData['is_active']);
        $this->assertEquals($organization->parentOrganization->name->description, $frontendData['parent']['en']);
        $this->assertEquals($organization->shortName->description, $frontendData['short_name']['en']);
    }

    /**
     * @test
     */
    function a_accommodation_has_devices()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['devices']);

        $this->assertEquals(count($organization->devices), count($frontendData['devices']['room']));

        foreach ($organization->devices as $device) {
            $deviceData = array_shift($frontendData['devices']['room']);
            $this->assertEquals($device->name->name, $deviceData['name']['en']);
            $this->assertEquals(count($device->usages), count($deviceData['usages']));
        }
    }

    /**
     * @test
     */
    function a_accommodation_has_prices()
    {
        list(, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['devices', 'prices']);

        foreach ($frontendData['devices']['room'] as $deviceData) {
            $this->assertTrue(isset($deviceData['products']));
            foreach ($deviceData['products'] as $prod) {
                $this->assertTrue(isset($prod['prices']));
            }
        }
    }

    /**
     * @test
     */
    function a_accommodation_has_date_ranges()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['date_ranges']);

        $this->assertCount(3, $frontendData['date_ranges']);

        $openDateRanges = $organization->dateRanges()->open()->orderBy('from_time')->get();
        $this->assertEquals(count($openDateRanges), count($frontendData['date_ranges']['open']));
        foreach ($openDateRanges as $dateRange) {
            $dateRangeData = array_shift($frontendData['date_ranges']['open']);
            $this->assertEqualDateRangeAndData($dateRange, $dateRangeData);
        }

        $closedDateRanges = $organization->dateRanges()->closed()->orderBy('from_time')->get();
        $this->assertEquals(count($closedDateRanges), count($frontendData['date_ranges']['closed']));
        foreach ($closedDateRanges as $dateRange) {
            $dateRangeData = array_shift($frontendData['date_ranges']['closed']);
            $this->assertEqualDateRangeAndData($dateRange, $dateRangeData);
        }

        $priceModifierDateRanges = $organization->dateRanges()->priceModifier()->orderBy('from_time')->get();
        $this->assertEquals(count($priceModifierDateRanges), count($frontendData['date_ranges']['price_modifier']));
        foreach ($priceModifierDateRanges as $dateRange) {
            $dateRangeData = array_shift($frontendData['date_ranges']['price_modifier']);
            $this->assertEqualDateRangeAndData($dateRange, $dateRangeData);
        }
    }

    function assertEqualDateRangeAndData(DateRange $dateRange, array $dateRangeData)
    {
        $this->assertEquals($dateRange->id, $dateRangeData['id']);
        if ($dateRange->name) {
            $this->assertEquals($dateRange->name->description, $dateRangeData['name']['en']);
        } else {
            $this->assertNull($dateRangeData['name']);
        }
        $this->assertEquals($dateRange->from_time, $dateRangeData['from_date']);
        $this->assertEquals($dateRange->to_time, $dateRangeData['to_date']);
        $this->assertEquals($dateRange->type->name, $dateRangeData['type']);
        $this->assertEquals($dateRange->marginType ? $dateRange->marginType->name : null, $dateRangeData['margin_type']);
        $this->assertEquals($dateRange->margin_value, $dateRangeData['margin_value']);
    }

    /**
     * @test
     */
    function a_accommodation_has_availability_mode()
    {
        list(, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['availability_mode']);

        $this->assertEquals('exact', $frontendData['availability_mode']);
    }

    /**
     * @test
     */
    function a_accommodation_has_pricing_info()
    {
        list(, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['pricing']);

        $this->assertEquals('from_net_price', $frontendData['pricing_logic']);
        $this->assertEquals('percentage', $frontendData['margin_type']);
    }
}
