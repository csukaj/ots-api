<?php

namespace Tests\Integration\Entities;

use App\Entities\SupplierEntity;
use App\Supplier;
use Modules\Stylerscontact\Entities\ContactEntity;
use Modules\Stylerscontact\Entities\PersonEntity;
use Tests\TestCase;

class SupplierEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity(): array
    {
        $supplier = Supplier::first();
        return [$supplier, (new SupplierEntity($supplier))];
    }

    /**
     * @test
     */
    function it_can_present_organization_data()
    {
        list($supplier, $supplierEntity) = $this->prepare_model_and_entity();
        $frontendData = $supplierEntity->getFrontendData();

        $this->assertEquals($supplier->id, $frontendData['id']);
        $this->assertEquals($supplier->name->description, $frontendData['name']['en']);
    }

    /**
     * @test
     */
    function it_can_present_organization_data_with_children()
    {
        list($supplier, $supplierEntity) = $this->prepare_model_and_entity();
        $frontendData = $supplierEntity->getFrontendData(['contacts', 'people']);

        $this->assertEquals($supplier->id, $frontendData['id']);
        $this->assertEquals($supplier->name->description, $frontendData['name']['en']);
        $this->assertEquals(ContactEntity::getCollection($supplier->contacts), $frontendData['contacts']);
        $this->assertEquals(PersonEntity::getCollection($supplier->people,['contacts']), $frontendData['people']);
    }
}
