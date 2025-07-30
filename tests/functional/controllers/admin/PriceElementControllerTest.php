<?php

namespace Tests\Functional\Controllers\Admin;

use App\AgeRange;
use App\DateRange;
use App\Device;
use App\DeviceUsage;
use App\Entities\PriceEntity;
use App\ModelMealPlan;
use App\Organization;
use App\Price;
use App\PriceElement;
use App\Product;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class PriceElementControllerTest extends TestCase
{
    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    private function prepare_models_and_entity()
    {
        $organization = factory(Organization::class, 'accommodation')->create([
            'margin_type_taxonomy_id' => Taxonomy::getTaxonomy('value', Config::get('taxonomies.margin_type'))->id
        ]);
        $device = factory(Device::class, 'room')->create([
            'deviceable_type' => Organization::class,
            'deviceable_id' => $organization->id
        ]);
        $usage = factory(DeviceUsage::class)->create(['device_id' => $device->id]);
        $product = factory(Product::class)->create([
            'productable_id' => $device->id,
            'productable_type' => Device::class
        ]);
        $dateRange = factory(DateRange::class)->create([
            'date_rangeable_type' => get_class($organization),
            'date_rangeable_id' => $organization->id,
            'from_time' => '2026-01-01 12:00:00',
            'to_time' => '2026-02-01 12:00:00'
        ]);
        $ageRangeAdult = factory(AgeRange::class)->create([
            'age_rangeable_type' => get_class($organization),
            'age_rangeable_id' => $organization->id,
            'from_age' => 5,
            'to_age' => null,
            'name_taxonomy_id' => Config::get('taxonomies.age_ranges.adult.id')
        ]);
        $mMealPlan = factory(ModelMealPlan::class)->create([
            'meal_planable_type' => Organization::class,
            'meal_planable_id' => $organization->id,
            'date_range_id' => $dateRange->id
        ]);
        $mMealPlan2 = factory(ModelMealPlan::class)->create([
            'meal_planable_type' => Organization::class,
            'meal_planable_id' => $organization->id,
            'date_range_id' => $dateRange->id
        ]);
        $price = factory(Price::class)->create([
            'product_id' => $product->id,
            'age_range_id' => $ageRangeAdult->id
        ]);
        $priceElement = factory(PriceElement::class)->create([
            'price_id' => $price->id,
            'model_meal_plan_id' => $mMealPlan->id,
            'date_range_id' => $dateRange->id
        ]);
        return [
            $organization,
            $device,
            $usage,
            $price,
            $priceElement,
            (new PriceEntity($price)),
            $mMealPlan,
            $mMealPlan2
        ];
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_create_many_elements()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , , $price, $priceElement, , $orgMealPlan, $orgMealPlan2) = $this->prepare_models_and_entity();

        $data = [
            'data' =>
                [
                    [
                        'date_range_id' => $priceElement->date_range_id,
                        'price_id' => $price->id,
                        'meal_plan' => $orgMealPlan->mealPlan->name->name,
                        'net_price' => 10,
                        'rack_price' => null,
                        'margin_type' => 'percentage',
                        'margin_value' => 22,
                        'enabled' => true
                    ],
                    [
                        'date_range_id' => $priceElement->date_range_id,
                        'price_id' => $price->id,
                        'meal_plan' => $orgMealPlan2->mealPlan->name->name,
                        'net_price' => 10,
                        'rack_price' => null,
                        'margin_type' => 'percentage',
                        'margin_value' => 22,
                        'enabled' => true
                    ]
                ]
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price-element/update-collection", 'POST', $token, $data);
        $this->assertEquals(count($data['data']), count($responseData->data));

        foreach ($responseData->data as $idx => $elementData) {
            $this->assertTrue(!!$elementData->id);
            $this->assertEquals($data['data'][$idx]['date_range_id'], $elementData->date_range_id);
            $this->assertEquals($data['data'][$idx]['price_id'], $elementData->price_id);
            $this->assertEquals($data['data'][$idx]['net_price'], $elementData->net_price);
            $this->assertEquals($data['data'][$idx]['margin_value'], $elementData->margin_value);
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_update_many_elements()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , , $price, $priceElement, , $orgMealPlan, ) = $this->prepare_models_and_entity();

        $data = [
            'data' =>
                [
                    [
                        'date_range_id' => $priceElement->date_range_id,
                        'price_id' => $price->id,
                        'meal_plan' => $orgMealPlan->mealPlan->name->name,
                        'net_price' => 10,
                        'rack_price' => null,
                        'margin_type' => 'percentage',
                        'margin_value' => 22,
                        'enabled' => true
                    ],
                    [
                        'date_range_id' => $priceElement->date_range_id,
                        'price_id' => $price->id,
                        'meal_plan' => $orgMealPlan->mealPlan->name->name,
                        'net_price' => 15,
                        'rack_price' => null,
                        'margin_type' => 'percentage',
                        'margin_value' => 20,
                        'enabled' => true
                    ]
                ]
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price-element/update-collection", 'POST', $token, $data);
        $this->assertEquals(count($data['data']), count($responseData->data));

        foreach ($responseData->data as $idx => $elementData) {
            $this->assertTrue(!!$elementData->id);
            $this->assertEquals($data['data'][$idx]['date_range_id'], $elementData->date_range_id);
            $this->assertEquals($data['data'][$idx]['price_id'], $elementData->price_id);
            $this->assertEquals($data['data'][$idx]['net_price'], $elementData->net_price);
            $this->assertEquals($data['data'][$idx]['margin_value'], $elementData->margin_value);
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_update_same_data_in_different_requests()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , , $price, $priceElement, , $orgMealPlan) = $this->prepare_models_and_entity();

        $data = [
            'data' =>
                [
                    [
                        'date_range_id' => $priceElement->date_range_id,
                        'price_id' => $price->id,
                        'meal_plan' => $orgMealPlan->mealPlan->name->name,
                        'net_price' => 10,
                        'rack_price' => null,
                        'margin_type' => 'percentage',
                        'margin_value' => 22,
                        'enabled' => true
                    ]
                ]
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price-element/update-collection", 'POST', $token, $data);
        $this->assertEquals(count($data['data']), count($responseData->data));

        foreach ($responseData->data as $idx => $elementData) {
            $this->assertTrue(!!$elementData->id);
            $this->assertEquals($data['data'][$idx]['date_range_id'], $elementData->date_range_id);
            $this->assertEquals($data['data'][$idx]['price_id'], $elementData->price_id);
            $this->assertEquals($data['data'][$idx]['net_price'], $elementData->net_price);
            $this->assertEquals($data['data'][$idx]['margin_value'], $elementData->margin_value);
        }

        $responseData2 = $this->assertSuccessfulHttpApiRequest("/admin/price-element/update-collection", 'POST', $token, $data);
        $this->assertEquals(count($data['data']), count($responseData2->data));

        foreach ($responseData2->data as $idx => $elementData) {
            $this->assertTrue(!!$elementData->id);
            $this->assertEquals($data['data'][$idx]['date_range_id'], $elementData->date_range_id);
            $this->assertEquals($data['data'][$idx]['price_id'], $elementData->price_id);
            $this->assertEquals($data['data'][$idx]['net_price'], $elementData->net_price);
            $this->assertEquals($data['data'][$idx]['margin_value'], $elementData->margin_value);
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_not_restores_disabled_data_()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , , $price, $priceElement, , $orgMealPlan) = $this->prepare_models_and_entity();

        $data = [
            'data' =>
                [
                    [
                        'date_range_id' => $priceElement->date_range_id,
                        'price_id' => $price->id,
                        'meal_plan' => $orgMealPlan->mealPlan->name->name,
                        'net_price' => 10,
                        'rack_price' => null,
                        'margin_type' => 'percentage',
                        'margin_value' => 22,
                        'enabled' => true
                    ]
                ]
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price-element/update-collection", 'POST', $token, $data);
        $this->assertCount(1, $responseData->data);
        $this->assertTrue(!!$responseData->data[0]->id);


        $data['data'][0]['enabled'] = false;

        $responseData2 = $this->assertSuccessfulHttpApiRequest("/admin/price-element/update-collection", 'POST', $token, $data);
        $this->assertCount(1, $responseData2->data);

        $elementData = $responseData2->data[0];
        $expected = $data['data'][0];
        $this->assertTrue(!!$elementData->id);
        $this->assertEquals($expected['date_range_id'], $elementData->date_range_id);
        $this->assertEquals($expected['price_id'], $elementData->price_id);
        $this->assertEquals($expected['net_price'], $elementData->net_price);
        $this->assertEquals($expected['margin_value'], $elementData->margin_value);


        $responseData3 = $this->assertSuccessfulHttpApiRequest("/admin/price-element/update-collection", 'POST', $token, $data);
        $this->assertCount(1, $responseData3->data);

        list(, , $response4) = $this->httpApiRequest("/admin/price-element/" . $responseData3->data[0]->id,
            'GET', $token);
        $response4->assertStatus(404);
    }
}