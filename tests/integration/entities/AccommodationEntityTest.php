<?php

namespace Tests\Integration\Entities;

use App\Accommodation;
use App\DateRange;
use App\Entities\AccommodationEntity;
use App\Entities\AgeRangeEntity;
use App\Entities\MealPlanEntity;
use App\Entities\OrganizationClassificationEntity;
use App\Entities\OrganizationMetaEntity;
use App\OrganizationClassification;
use App\OrganizationMeta;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class AccommodationEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @param int $id
     * @return array
     */
    private function prepare_model_and_entity($id = 1): array
    {
        $accommodation = Accommodation::findOrFail($id);
        return [$accommodation, (new AccommodationEntity($accommodation))];
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
        $this->assertEquals($organization->parentOrganization->name->description, $frontendData['parent']['name']['en']);
    }

    /**
     * @test
     */
    function an_accommodation_has_devices()
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
    function an_accommodation_has_public_devices()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['info']);

        $this->assertEquals(count($organization->devices), count($frontendData['devices']['room']));

        foreach ($organization->devices as $device) {
            $deviceData = array_shift($frontendData['devices']['room']);
            $this->assertEquals($device->name->name, $deviceData['name']['en']);
        }
    }

    /**
     * @test
     */
    function an_accommodation_has_prices()
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
    function an_accommodation_has_date_ranges()
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
    function an_accommodation_has_a_location()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['info']);

        $this->assertTrue(!empty($frontendData['location']));
        $this->assertEquals($organization->location->island->name->name, $frontendData['location']['island']);
    }

    /**
     * @test
     */
    function an_accommodation_has_availability_mode()
    {
        list(, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['availability_mode']);

        $this->assertEquals('exact', $frontendData['availability_mode']);
    }

    /**
     * @test
     */
    function an_accommodation_has_pricing_info()
    {
        list(, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['pricing']);

        $this->assertEquals('from_net_price', $frontendData['pricing_logic']);
        $this->assertEquals('percentage', $frontendData['margin_type']);
    }

    /**
     * @test
     */
    function an_accommodation_has_descriptions()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['info']);

        $this->assertTrue(!empty($frontendData['descriptions']));

        foreach ($organization->descriptions as $description) {
            $descriptionTxName = $description->descriptionTaxonomy->name;
            $desc = (new DescriptionEntity($description->description))->getFrontendData();
            $this->assertEquals($desc, $frontendData['descriptions'][$descriptionTxName]);
        }
    }

    /**
     * @test
     */
    function an_accommodation_has_meta_and_classifications()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['info']);
        $metas = (new OrganizationMeta())->where('organization_id', $organization->id)->listable()->forParent(null)->orderBy('priority')->get();
        $mtEntities = OrganizationMetaEntity::getCollection($metas, ['frontend']);
        $classifications = (new OrganizationClassification())->where('organization_id', $organization->id)->listable()->forParent(null)->orderBy('priority')->get();
        $clsEntities = OrganizationClassificationEntity::getCollection($classifications, ['frontend']);
        $this->assertEquals(array_merge($mtEntities, $clsEntities), $frontendData['properties']);
    }

    /**
     * @test
     */
    function an_accommodation_has_galleries()
    {
        list(, $organizationEntity) = $this->prepare_model_and_entity();
        $this->assertTrue(!empty($organizationEntity->getFrontendData(['info'])['galleries']));
    }

    /**
     * @test
     */
    function an_accommodation_has_age_ranges()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['info']);

        $ageRanges = AgeRangeEntity::getCollection($organization->ageRanges, ['frontend']);

        $this->assertEquals(count($ageRanges), count($frontendData['age_ranges']));
        $this->assertEquals($ageRanges, $frontendData['age_ranges']);
    }

    /**
     * @test
     */
    function an_accommodation_has_meal_plans()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['info']);

        $mealPlans = [];
        foreach ($organization->modelMealPlans as $modelMealPlan) {
            $mealPlans[] = (new MealPlanEntity($modelMealPlan->mealPlan))->getFrontendData();
        }
        $mealPlans = array_unique($mealPlans, SORT_REGULAR);

        $this->assertEquals(count($mealPlans), count($frontendData['meal_plans']));
        $this->assertEquals($mealPlans, $frontendData['meal_plans']);
    }

    /**
     * @test
     */
    function an_accommodation_has_search_options()
    {
        list($organization, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['info']);
        $actual = $frontendData['search_options'];

        $expected = [];
        if (!empty($organization->parentOrganization)) {
            $expected['Hotel Chain'] = $organization->parentOrganization->name->description;
        }
        $orgCls = OrganizationClassification::searchable()->where('organization_id', '=', $organization->id)->get();
        foreach ($orgCls as $orgCl) {
            $expected[$orgCl->classificationTaxonomy->name] = ($orgCl->valueTaxonomy) ? $orgCl->valueTaxonomy->name : true;
        }

        $this->assertEquals(count($expected), count($actual));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    function an_accommodation_has_availability_update_info()
    {
        list(, $organizationEntity) = $this->prepare_model_and_entity(21);
        $frontendData = $organizationEntity->getFrontendData(['availability_update']);
        $this->assertEquals('Hotel Link Solutions', $frontendData['availability_update']['channel_manager']);
        $this->assertNotEmpty($frontendData['availability_update']['last_updated_at']);
        $this->assertRegExp('/^20\d\d-\d\d-\d\dT\d\d:\d\d:\d\dZ$/',$frontendData['availability_update']['last_updated_at']); //ISO8601
        $this->assertTrue(isset($frontendData['availability_update']['last_updated_until']));


        list(, $organizationEntity) = $this->prepare_model_and_entity();
        $frontendData = $organizationEntity->getFrontendData(['availability_update']);
        $this->assertNull($frontendData['availability_update']['channel_manager']);
        $this->assertNotEmpty($frontendData['availability_update']['last_updated_at']);
        $this->assertRegExp('/^20\d\d-\d\d-\d\dT\d\d:\d\d:\d\dZ$/',$frontendData['availability_update']['last_updated_at']); //ISO8601
        $this->assertNull($frontendData['availability_update']['last_updated_until']);

    }
}
