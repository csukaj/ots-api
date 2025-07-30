<?php

namespace Tests\Integration\Manipulators;

use App\Device;
use App\DeviceUsage;
use App\Facades\Config;
use App\Manipulators\PriceSetter;
use App\Organization;
use App\AgeRange;
use App\DateRange;
use App\ModelMealPlan;
use App\Price;
use App\Product;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class PriceSetterTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    
    private function prepare() {
        
            $organization = factory(Organization::class, 'accommodation')->create([
                'margin_type_taxonomy_id' => Config::get('taxonomies.margin_types.value')
            ]);
            $device = factory(Device::class, 'room')->create(['deviceable_id' => $organization->id, 'deviceable_type' => Organization::class]);
            $product = factory(Product::class)->create(['productable_id' => $device->id, 'productable_type' => Device::class]);
            $orgDateRange = factory(DateRange::class)->create([
                'date_rangeable_id' => $organization->id,
                'from_time' => '2026-01-01 12:00:00',
                'to_time' => '2026-02-01 12:00:00'
            ]);
            $orgAgeRangeAdult = factory(AgeRange::class)->create([
                'age_rangeable_id' => $organization->id,
                'from_age' => 5,
                'to_age' => null,
                'name_taxonomy_id' => Config::get('taxonomies.age_ranges.adult.id')
            ]);
            $orgAgeRangeBaby = factory(AgeRange::class)->create([
                'age_rangeable_id' => $organization->id,
                'from_age' => 0,
                'to_age' => 5,
                'name_taxonomy_id' => Config::get('taxonomies.age_ranges.baby.id')
            ]);
            $orgMealPlan = factory(ModelMealPlan::class)->create([
                'meal_planable_type' => Organization::class,
                'meal_planable_id' => $organization->id,
                'date_range_id' => $orgDateRange->id
            ]);

        
        return [$organization, $device, $product, $orgAgeRangeAdult, $orgAgeRangeBaby, $orgMealPlan];
    }

    /**
     * @test
     */
    public function it_can_set_a_first_base_price() {
        list($organization, $device, $product, $orgAgeRangeAdult, $orgAgeRangeBaby, $orgMealPlan) = $this->prepare();
        $nameTx = Taxonomy::getOrCreateTaxonomy('Single', Config::getOrFail('taxonomies.names.price_name'));
        $price = (new PriceSetter([
            'product_id' => $product->id,
            'age_range_id' => $orgAgeRangeAdult->id,
            'name_taxonomy_id' => $nameTx->id,
            'amount' => 1,
            'margin_type_taxonomy_id' => Config::getOrFail('taxonomies.margin_types.percentage'),
            'margin_value' => 10,
            'extra' => false
        ]))->set();
        
        $this->assertTrue(!!$price->id);
        $this->assertEquals('Single', $price->name->name);
        $this->assertTrue($price->mandatory);
    }
    
    /**
     * @test
     */
    public function it_can_set_a_second_base_price() {
        list($organization, $device, $product, $orgAgeRangeAdult, $orgAgeRangeBaby, $orgMealPlan) = $this->prepare();
        
        $nameTx = Taxonomy::getOrCreateTaxonomy('Single', Config::getOrFail('taxonomies.names.price_name'));
        $price = (new PriceSetter([
            'product_id' => $product->id,
            'age_range_id' => $orgAgeRangeAdult->id,
            'name_taxonomy_id' => $nameTx->id,
            'amount' => 1,
            'margin_type_taxonomy_id' => Config::getOrFail('taxonomies.margin_types.percentage'),
            'margin_value' => 10,
            'extra' => false
        ]))->set();
        
        $nameTx = Taxonomy::getOrCreateTaxonomy('Double', Config::getOrFail('taxonomies.names.price_name'));
        $price = (new PriceSetter([
            'product_id' => $product->id,
            'age_range_id' => $orgAgeRangeAdult->id,
            'name_taxonomy_id' => $nameTx->id,
            'amount' => 2,
            'margin_type_taxonomy_id' => Config::getOrFail('taxonomies.margin_types.percentage'),
            'margin_value' => 10,
            'extra' => false
        ]))->set();
        
        $this->assertTrue(!!$price->id);
        $this->assertEquals('Double', $price->name->name);
        $this->assertFalse($price->mandatory);
    }
    
    /**
     * @test
     */
    public function there_can_be_only_one_mandatory() {
        list($organization, $device, $product, $orgAgeRangeAdult, $orgAgeRangeBaby, $orgMealPlan) = $this->prepare();
        
                $nameTx = Taxonomy::getOrCreateTaxonomy('Single', Config::getOrFail('taxonomies.names.price_name'));
        $price = (new PriceSetter([
            'product_id' => $product->id,
            'age_range_id' => $orgAgeRangeAdult->id,
            'name_taxonomy_id' => $nameTx->id,
            'amount' => 1,
            'margin_type_taxonomy_id' => Config::getOrFail('taxonomies.margin_types.percentage'),
            'margin_value' => 10,
            'extra' => false
        ]))->set();
        
        $nameTx = Taxonomy::getOrCreateTaxonomy('Double', Config::getOrFail('taxonomies.names.price_name'));
        $price = (new PriceSetter([
            'product_id' => $product->id,
            'age_range_id' => $orgAgeRangeAdult->id,
            'name_taxonomy_id' => $nameTx->id,
            'amount' => 2,
            'margin_type_taxonomy_id' => Config::getOrFail('taxonomies.margin_types.percentage'),
            'margin_value' => 10,
            'extra' => false
        ]))->set();

        
        $nameTx = Taxonomy::getOrCreateTaxonomy('Triple', Config::getOrFail('taxonomies.names.price_name'));
        $newPrice = (new PriceSetter([
            'product_id' => $product->id,
            'age_range_id' => $orgAgeRangeAdult->id,
            'name_taxonomy_id' => $nameTx->id,
            'amount' => 3,
            'margin_type_taxonomy_id' => Config::getOrFail('taxonomies.margin_types.percentage'),
            'margin_value' => 10,
            'extra' => false,
            'mandatory' => true
        ]))->set();
        
        $this->assertTrue(!!$newPrice->id);
        $this->assertEquals('Triple', $newPrice->name->name);
        $this->assertTrue($newPrice->mandatory);
        
        $this->assertEquals(3, count($product->prices));
        foreach ($product->prices as $price) {
            $this->assertTrue(($price->id == $newPrice->id) || !$price->mandatory);
        }
    }
    
     /**
     * @test
     */
    function it_can_restored() {
        list(, , $product, $orgAgeRangeAdult, , ) = $this->prepare();
        $nameTx = Taxonomy::getOrCreateTaxonomy('Quadrouple', Config::getOrFail('taxonomies.names.price_name'));
        $data = [
            'product_id' => $product->id,
            'age_range_id' => $orgAgeRangeAdult->id,
            'name_taxonomy_id' => $nameTx->id,
            'amount' => 3,
            'margin_type_taxonomy_id' => Config::getOrFail('taxonomies.margin_types.percentage'),
            'margin_value' => 10,
            'extra' => false,
            'mandatory' => true
        ];
        $newPrice = (new PriceSetter($data))->set();
        
        $this->assertTrue((bool)Price::destroy($newPrice->id));
        
        $priceRestored = (new PriceSetter($data))->set();
        $this->assertEquals($newPrice->id, $priceRestored->id);
        $this->assertEquals($data['name_taxonomy_id'], $priceRestored->name_taxonomy_id);
    }
    
}
