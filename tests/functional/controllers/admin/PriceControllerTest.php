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

class PriceControllerTest extends TestCase
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
        $ageRangeBaby = factory(AgeRange::class)->create([
            'age_rangeable_type' => get_class($organization),
            'age_rangeable_id' => $organization->id,
            'from_age' => 0,
            'to_age' => 5,
            'name_taxonomy_id' => Config::get('taxonomies.age_ranges.baby.id')
        ]);
        $orgMealPlan = factory(ModelMealPlan::class)->create([
            'meal_planable_type' => get_class($organization),
            'meal_planable_id' => $organization->id,
            'date_range_id' => $dateRange->id
        ]);
        $priceNameTx = factory(Taxonomy::class)->create();
        $price = factory(Price::class)->create([
            'product_id' => $product->id,
            'age_range_id' => $ageRangeAdult->id,
            'name_taxonomy_id' => $priceNameTx->id
        ]);
        $priceElement = factory(PriceElement::class)->create([
            'price_id' => $price->id,
            'model_meal_plan_id' => $orgMealPlan->id,
            'date_range_id' => $dateRange->id
        ]);
        return [$organization, $device, $usage, $price, $priceElement, (new PriceEntity($price)), $product];
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_show_a_price()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , , $price, , $priceEntity) = $this->prepare_models_and_entity();

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price/{$price->id}", 'GET', $token, [], true);
        $this->assertEquals($priceEntity->getFrontendData(['admin']), $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_a_new_normal_price()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, $device, , , , , $product) = $this->prepare_models_and_entity();

        $data = [
            'device_id' => $device->id,
            'name' => ['name' => 'Single', 'translations' => ['en' => 'Single']],
            'age_range' => 'adult',
            'amount' => 1,
            'product_id' => $product->id,
            'extra' => false
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/price', 'POST', $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals('Single', $responseData->data->name->name);
        $this->assertEquals('adult', $responseData->data->age_range);
        $this->assertEquals(1, $responseData->data->amount);
        $this->assertEquals(false, (bool)$responseData->data->extra);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_a_new_extra_price()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, $device, , , , , $product) = $this->prepare_models_and_entity();

        $data = [
            'device_id' => $device->id,
            'name' => ['name' => 'Extra Baby', 'translations' => ['en' => 'Extra Baby']],
            'age_range' => 'baby',
            'amount' => 1,
            'product_id' => $product->id,
            'extra' => true
        ];
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/price', 'POST', $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals('Extra Baby', $responseData->data->name->name);
        $this->assertEquals('baby', $responseData->data->age_range);
        $this->assertEquals(1, $responseData->data->amount);
        $this->assertEquals(true, $responseData->data->extra);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_edit_a_price()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , , $price, , , $product) = $this->prepare_models_and_entity();

        $data = [
            'id' => $price->id,
            'name' => ['name' => 'Extra2 Baby', 'translations' => ['en' => 'Extra2 Baby']],
            'age_range' => 'baby',
            'amount' => 1,
            'product_id' => $product->id,
            'extra' => true
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price/{$price->id}", 'PUT', $token, $data);
        $this->assertEquals($data['id'], $responseData->data->id);
        $this->assertEquals($data['name']['name'], $responseData->data->name->name);
        $this->assertEquals($data['age_range'], $responseData->data->age_range);
        $this->assertEquals($data['amount'], $responseData->data->amount);
        $this->assertEquals($data['extra'], $responseData->data->extra);
    }


    /**
     * @test
     * @group controller-write
     */
    public function it_can_edit_a_price_with_elements()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list(, , , $price, , , $product) = $this->prepare_models_and_entity();

        $pelement = $price->elements->first();

        $element['meal_plan'] = $pelement->modelMealPlan->mealPlan->name;
        $element['margin_type'] = 'percentage';
        $element = [
            'date_range_id' => $pelement->date_range_id,
            'enabled' => true,
            'id' => $pelement->id,
            'margin_type' => $pelement->marginType->name,
            'margin_value' => $pelement->margin_value,
            'meal_plan' => $pelement->modelMealPlan->mealPlan->name->name,
            'net_price' => '1000',
            'rack_price' => '1000',
            'price_id' => $pelement['price_id'],
        ];


        $data = [
            'id' => $price->id,
            'name' => ['name' => 'Extra2 Baby', 'translations' => ['en' => 'Extra2 Baby']],
            'age_range' => 'baby',
            'amount' => 1,
            'product_id' => $product->id,
            'extra' => true,
            'elements' => [$element]
        ];


        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price/{$price->id}", 'PUT', $token, $data,
            true);
        unset($data["elements"][0]["enabled"]);

        //test if enabled element is saved correctly
        $this->assertEquals($data['id'], $responseData['data']['id']);
        $this->assertEquals($data['elements'], $responseData['data']['elements']);

        $data["elements"][0]["enabled"] = false;

        //test if disabled element is deleted
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price/{$price->id}", 'PUT', $token, $data);
        $this->assertEquals($data['id'], $responseData->data->id);
        $this->assertEquals([], $responseData->data->elements);
    }

}
