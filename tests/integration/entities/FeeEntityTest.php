<?php
namespace Tests\Integration\Entities;

use App\Entities\FeeEntity;
use App\Fee;
use Tests\TestCase;

class FeeEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_models_and_entity(): array
    {
        $fee = Fee::all()[1];
        return [$fee, (new FeeEntity($fee))];
    }

    /**
     * @test
     */
    function a_fee_has_public_data()
    {
        list($fee, $feeEntity) = $this->prepare_models_and_entity();

        $feeData = $feeEntity->getFrontendData();
        $this->assertEquals($fee->product->type->name, $feeData['type']);
        $this->assertEquals($fee->product->name->description, $feeData['name']['en']);
        $this->assertEquals($fee->ageRange->name->name, $feeData['age_range']);
        $this->assertEquals($fee->rack_price, $feeData['rack_price']);
    }

    /**
     * @test
     */
    function a_fee_has_admin_data()
    {
        list($fee, $feeEntity) = $this->prepare_models_and_entity();

        $feeData = $feeEntity->getFrontendData(['admin']);
        $this->assertEquals($fee->id, $feeData['id']);
        $this->assertEquals($fee->product->name->description, $feeData['name']['en']);
        $this->assertEquals($fee->ageRange->name->name, $feeData['age_range']);
        $this->assertEquals($fee->rack_price, $feeData['rack_price']);

        $this->assertEquals($fee->product_id, $feeData['product_id']);
        $this->assertEquals($fee->net_price, $feeData['net_price']);
        $this->assertEquals($fee->margin_type, $feeData['margin_type']);
        $this->assertEquals($fee->margin_value, $feeData['margin_value']);
    }
}
