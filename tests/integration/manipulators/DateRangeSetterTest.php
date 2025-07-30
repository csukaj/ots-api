<?php

namespace Tests\Integration\Manipulators;

use App\DateRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Manipulators\DateRangeSetter;
use App\Organization;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class DateRangeSetterTest extends TestCase
{

    protected function prepare_organization()
    {
        return factory(Organization::class, 'accommodation')->create([
            'margin_type_taxonomy_id' => Taxonomy::getTaxonomy('percentage', Config::get('taxonomies.margin_type'))->id
        ]);
    }

    /**
     * @test
     */
    function it_can_be_set()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-03',
            'type' => 'open',
            'margin_value' => 11
        ];
        $dateRange = (new DateRangeSetter($data))->set();

        $this->assertTrue(!!$dateRange->id);
        $this->assertEquals($nameDescription['en'], $dateRange->name->description);
        $this->assertEquals(get_class($organization), $dateRange->date_rangeable_type);
        $this->assertEquals($organization->id, $dateRange->date_rangeable_id);
        $this->assertEquals('2026-01-02', $dateRange->from_time);
        $this->assertEquals('2026-01-03', $dateRange->to_time);
        $this->assertEquals('open', $dateRange->type->name);
        $this->assertEquals('percentage', $dateRange->marginType->name);
        $this->assertEquals(11, $dateRange->margin_value);
    }

    /**
     * @test
     */
    function it_cannot_be_set_overlapped()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-03',
            'type' => 'open',
            'margin_value' => 11
        ];
        (new DateRangeSetter($data))->set();

        $this->expectException(UserException::class);
        (new DateRangeSetter($data))->set();
    }

    function it_can_be_1_day_long()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-02',
            'type' => 'open',
            'margin_value' => 11
        ];
        $dateRange = (new DateRangeSetter($data))->set();

        $this->assertTrue(!!$dateRange->id);
        $this->assertEquals($nameDescription, $dateRange->name->description);
        $this->assertEquals($organization->id, $dateRange->organization_id);
        $this->assertEquals('2026-01-02', $dateRange->from_time);
        $this->assertEquals('2026-01-02', $dateRange->to_time);
        $this->assertEquals('open', $dateRange->type->name);
        $this->assertEquals('percentage', $dateRange->marginType->name);
        $this->assertEquals(11, $dateRange->margin_value);
    }

    /**
     * @test
     */
    function it_cannot_be_set_with_invalid_daterange()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $this->expectException(UserException::class);

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2025-01-02',
            'to_date' => '2025-01-01',
            'type' => 'open',
            'margin_value' => 11
        ];
        (new DateRangeSetter($data))->set();

        $data['to_date'] = '2025-01-01';
        (new DateRangeSetter($data))->set();
    }

    /**
     * @test
     */
    function it_can_have_meal_plans()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $meals = ['e/p', 'h/b', 'inc'];

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-03',
            'type' => 'open',
            'margin_value' => 11,
            'meal_plans' => $meals
        ];

        $dateRange = (new DateRangeSetter($data))->set();
        $this->assertTrue(!!$dateRange->id);
        $mealPlans = $dateRange->modelMealPlans;
        $this->assertCount(3, $mealPlans);

        $allFound = true;
        for ($i = 0; $i < count($mealPlans); $i++) {
            $found = false;
            for ($j = 0; $j < count($meals); $j++) {
                if ($mealPlans[$i]->attributesToArray()['meal_plan_id'] == Config::get("taxonomies.meal_plans." . $meals[$j] . ".meal_plan_id")) {
                    $found = true;
                }
            }

            if (!$found) {
                $allFound = FALSE;
            }

            $this->assertTrue($allFound);

            if (!$allFound) {
                break;
            }
        }
    }

    /**
     * @test
     */
    function it_can_have_margin()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $type = 'percentage';
        $margin = 11;

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-03',
            'type' => 'open',
            'margin_type' => $type,
            'margin_value' => $margin,
            'meal_plans' => ['e/p', 'h/b', 'inc']
        ];

        $dateRange = (new DateRangeSetter($data))->set();
        $this->assertTrue(!!$dateRange->id);
        $this->assertEquals(Config::get("taxonomies.margin_types." . $type), $dateRange->margin_type_taxonomy_id);
        $this->assertEquals($margin, $dateRange->margin_value);
    }

    /**
     * @test
     */
    function it_can_have_type()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $type = 'closed';

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-03',
            'type' => $type,
            'margin_value' => 11,
            'meal_plans' => ['e/p', 'h/b', 'inc']
        ];

        $dateRange = (new DateRangeSetter($data))->set();
        $this->assertTrue(!!$dateRange->id);
        $this->assertEquals(Config::get("taxonomies.date_range_types." . $type), $dateRange->type_taxonomy_id);
    }

    /**
     * @test
     */
    function it_can_updated()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $type = 'closed';

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-03',
            'type' => $type,
            'margin_value' => 11,
            'meal_plans' => ['e/p', 'h/b', 'inc']
        ];

        $dateRange = (new DateRangeSetter($data))->set();
        $this->assertTrue(!!$dateRange->id);

        $data['id'] = $dateRange->id;
        $data['to_date'] = '2026-01-05';
        $dateRangeUpdated = (new DateRangeSetter($data))->set();
        $this->assertEquals($dateRange->id, $dateRangeUpdated->id);
        $this->assertEquals($data['to_date'], $dateRangeUpdated->to_time);
    }

    /**
     * @test
     */
    function it_can_have_minimum_nights()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $minimum_nights = 2;

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-03',
            'type' => 'open',
            'margin_value' => 11,
            'meal_plans' => ['e/p', 'h/b', 'inc'],
            'minimum_nights' => $minimum_nights
        ];

        $dateRange = (new DateRangeSetter($data))->set();
        $this->assertTrue(!!$dateRange->id);
        $this->assertEquals($minimum_nights, $dateRange->minimum_nights);
    }

    /**
     * @test
     */
    function it_can_restored()
    {
        $organization = $this->prepare_organization();
        $nameDescription = ['en' => $this->faker->word];

        $type = 'open';

        $data = [
            'name' => $nameDescription,
            'date_rangeable_type' => Organization::class,
            'date_rangeable_id' => $organization->id,
            'from_date' => '2026-01-02',
            'to_date' => '2026-01-03',
            'type' => $type,
            'margin_value' => 11,
            'meal_plans' => ['e/p', 'h/b', 'inc']
        ];

        $dateRange = (new DateRangeSetter($data))->set();
        $this->assertTrue(!!$dateRange->id);

        $this->assertTrue((bool)DateRange::destroy($dateRange->id));

        $dateRangeRestored = (new DateRangeSetter($data))->set();
        $this->assertEquals($dateRange->id, $dateRangeRestored->id);
        $this->assertEquals($data['to_date'], $dateRangeRestored->to_time);
    }

}
