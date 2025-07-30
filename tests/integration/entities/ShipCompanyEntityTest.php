<?php
namespace Tests\Integration\Entities;

use App\Entities\ShipCompanyEntity;
use App\ShipCompany;
use Tests\TestCase;

class ShipCompanyEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity(): array
    {
        $shipCompany = ShipCompany::all()->first();
        return [$shipCompany, (new ShipCompanyEntity($shipCompany))];
    }

    /**
     * @test
     */
    function it_can_present_organization_data()
    {
        list($shipCompany, $shipCompanyEntity) = $this->prepare_model_and_entity();
        $frontendData = $shipCompanyEntity->getFrontendData();

        $this->assertEquals($shipCompany->id, $frontendData['id']);
        $this->assertEquals($shipCompany->name->description, $frontendData['name']['en']);
    }

}
