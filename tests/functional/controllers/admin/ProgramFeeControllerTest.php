<?php
namespace Tests\Functional\Controllers\Admin;

use App\Facades\Config;
use App\Location;
use App\Product;
use App\Program;
use Tests\TestCase;
use function factory;

class ProgramFeeControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    /**
     * @test
     */
    public function it_can_show_a_program_fee()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $product = Product::where('productable_type', Program::class)->first();
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/program-fee/{$product->productable_id}", 'GET', $token);
        $this->assertEquals($product->id, $responseData->data->id);
    }

    /**
     * @test
     */
    public function it_can_store_program_fees()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $program = factory(Program::class)->create(['location_id' => Location::first()->id]);

        $data = [
            'productable_id' => $program->id,
            'productable_type' => Program::class,
            'type_taxonomy_id' => Config::getOrFail('taxonomies.product_types.group_fee'),
            'name_description' => ['en' => $this->faker->word],
            'fees' => [
                [
                    "rack_price" => 100
                ]
            ]
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/program-fee', 'POST', $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($data['productable_id'], $responseData->data->program_id);
        $this->assertEquals($data['fees'][0]['rack_price'], $responseData->data->fees[0]->rack_price);
    }

    /**
     * @test
     */
    public function it_can_update_a_program_fees()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $product = Product::where('productable_type', Program::class)->first();
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/program-fee/{$product->productable_id}", 'GET', $token, [], true);
        $this->assertEquals($product->id, $responseData['data']['id']);

        $data = $responseData['data'];

        $data['type_taxonomy_id'] = Config::getOrFail('taxonomies.product_types.'.$data['type']);
        $data['productable_id'] = $data['program_id'];
        $data['productable_type'] = Program::class;
        $data['fees'][0]['rack_price'] = 12345;

        $responseUpdateData = $this->assertSuccessfulHttpApiRequest("/admin/program-fee/{$product->id}", 'PUT', $token, $data);

        $this->assertEquals($data['id'], $responseUpdateData->data->id);
        $this->assertEquals($data['program_id'], $responseUpdateData->data->program_id);
        $this->assertEquals($data['fees'][0]['age_range'], $responseUpdateData->data->fees[0]->age_range);
        $this->assertEquals($data['fees'][0]['rack_price'], $responseUpdateData->data->fees[0]->rack_price);
    }
}
