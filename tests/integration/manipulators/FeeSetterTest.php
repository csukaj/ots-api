<?php
namespace Tests\Integration\Manipulators;

use App\AgeRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Fee;
use App\Location;
use App\Manipulators\FeeSetter;
use App\Product;
use App\Program;
use Tests\TestCase;
use function factory;

class FeeSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare(int $productTypeTxId): array
    {
        $program = factory(Program::class)->create(['location_id' => Location::first()->id]);

        $product = factory(Product::class)->create([
            'productable_id' => $program->id,
            'productable_type' => Program::class,
            'type_taxonomy_id' => $productTypeTxId
        ]);

        $ageRangeAdult = factory(AgeRange::class)->create([
            'age_rangeable_type' => Product::class,
            'age_rangeable_id' => $product->id,
            'from_age' => 6,
            'to_age' => null,
            'name_taxonomy_id' => Config::get('taxonomies.age_ranges.adult.id')
        ]);

        return [$product, $ageRangeAdult];
    }

    /**
     * @test
     */
    public function it_can_set_a_group_fee()
    {
        list($product) = $this->prepare(Config::getOrFail('taxonomies.product_types.group_fee'));
        $fee = (new FeeSetter([
            'product_id' => $product->id,
            'rack_price' => 10.1
            ]))->set();

        $this->assertTrue(!!$fee->id);
        $this->assertEquals(10.1, $fee->rack_price);
        $this->assertEquals($product->id, $fee->product_id);
    }

    /**
     * @test
     */
    public function it_can_set_a_personal_fee()
    {
        list($product, $ageRangeAdult) = $this->prepare(Config::getOrFail('taxonomies.product_types.personal_fee'));
        $fee = (new FeeSetter([
            'product_id' => $product->id,
            'age_range' => $ageRangeAdult->name->name,
            'rack_price' => 10.1
            ]))->set();

        $this->assertTrue(!!$fee->id);
        $this->assertEquals(10.1, $fee->rack_price);
        $this->assertEquals($product->id, $fee->product_id);
        $this->assertEquals($ageRangeAdult->id, $fee->age_range_id);
    }

    /**
     * @test
     */
    function it_can_modified()
    {
        list($product, $ageRangeAdult) = $this->prepare(Config::getOrFail('taxonomies.product_types.group_fee'));
        $data = [
            'product_id' => $product->id,
            'rack_price' => 10.1
        ];
        $fee = (new FeeSetter($data))->set();

        $this->assertTrue(!!$fee->id);

        $data['id'] = $fee->id;
        $data['rack_price'] = 12.3;

        $feeUpdated = (new FeeSetter($data))->set();
        $this->assertEquals($fee->id, $feeUpdated->id);
        $this->assertEquals($data['rack_price'], $feeUpdated->rack_price);
    }

    /**
     * @test
     */
    function it_can_restored()
    {
        list($product, $ageRangeAdult) = $this->prepare(Config::getOrFail('taxonomies.product_types.group_fee'));
        $data = [
            'product_id' => $product->id,
            'rack_price' => 10.1
        ];
        $fee = (new FeeSetter($data))->set();

        $this->assertTrue(!!$fee->id);

        $this->assertTrue((bool) Fee::destroy($fee->id));

        $feeRestored = (new FeeSetter($data))->set();
        $this->assertEquals($fee->id, $feeRestored->id);
        $this->assertEquals($data['rack_price'], $feeRestored->rack_price);
    }

    /**
     * @test
     */
    function it_cannot_set_fee_with_bad_input_data()
    {
        list($product, $ageRangeAdult) = $this->prepare(Config::getOrFail('taxonomies.product_types.personal_fee'));

        $this->expectException(UserException::class);
        $fee1 = (new FeeSetter([
            'product_id' => 999999999,
            'rack_price' => 10.1
        ]))->set();

        $this->expectException(UserException::class);
        $fee2 = (new FeeSetter([
            'product_id' => $product->id,
            'rack_price' => 10.1
        ]))->set();
        
        $this->expectException(UserException::class);
        $fee3 = (new FeeSetter([
            'product_id' => $product->id,
            'age_range' => 'aaaaadult',
            'rack_price' => 10.1
        ]))->set();
        
        $this->expectException(UserException::class);
        $fee4 = (new FeeSetter([
            'product_id' => $product->id,
            'age_range' => $ageRangeAdult->name->name,
            'rack_price' => 'NaN'
        ]))->set();
    }
}
