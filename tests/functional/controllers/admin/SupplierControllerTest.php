<?php

namespace Tests\Functional\Controllers\Admin;

use App\Entities\SupplierEntity;
use App\Facades\Config;
use App\Supplier;
use Tests\TestCase;

class SupplierControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    private function prepare_models_and_entity(): array
    {
        $supplier = Supplier::all()->first();
        return [$supplier, (new SupplierEntity($supplier))];
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_suppliers()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/supplier', 'GET', $token, [], true);

        $allSupplier = SupplierEntity::getCollection(Supplier::all(), ['contacts','people']);

        $this->assertEquals($allSupplier, $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_a_supplier()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($supplier, $supplierEntity) = $this->prepare_models_and_entity();

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/supplier/{$supplier->id}", 'GET', $token, [], true);
        $this->assertEquals($supplierEntity->getFrontendData(['contacts','people']), $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_a_new_supplier()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = ["name" => ['en' => $this->faker->word], 'type' => 'supplier',
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/supplier', 'POST', $token, $data);
        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals((object)$data['name'], $responseData->data->name);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_edit_a_supplier()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($supplier,) = $this->prepare_models_and_entity();

        $data = [
            "id" => $supplier->id,
            "name" => ['en' => $this->faker->word],
            'type' => 'supplier'
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/supplier/{$supplier->id}", 'PUT', $token,
            $data);

        $this->assertEquals($supplier->id, $responseData->data->id);
        $this->assertEquals((object)$data['name'], $responseData->data->name);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_delete_a_supplier()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = ["name" => ['en' => $this->faker->word], 'type' => 'supplier',
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]];

        $createResponseData = $this->assertSuccessfulHttpApiRequest('/admin/supplier', 'POST', $token,
            $data);

        $this->assertTrue(!!$createResponseData->data->id);
        $id = $createResponseData->data->id;

        $this->assertSuccessfulHttpApiRequest("/admin/supplier/{$id}", 'DELETE', $token);
        $this->assertNotEmpty(Supplier::onlyTrashed()->find($id));
    }
}
