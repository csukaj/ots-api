<?php
namespace Tests\Integration\Entities;

use App\Entities\ShipEntity;
use App\Entities\ShipGroupEntity;
use App\Ship;
use Tests\TestCase;

class ShipEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity(): array
    {
        $ship = Ship::all()->first();
        return [$ship, (new ShipEntity($ship))];
    }

    /**
     * @test
     */
    function it_can_present_organization_data()
    {
        list($ship, $shipEntity) = $this->prepare_model_and_entity();
        $frontendData = $shipEntity->getFrontendData();

        $this->assertEquals($ship->id, $frontendData['id']);
        $this->assertEquals($ship->name->description, $frontendData['name']['en']);
    }

    /**
     * @test
     */
    function it_can_present_organization_data_with_children()
    {
        list($ship, $shipEntity) = $this->prepare_model_and_entity();
        $frontendData = $shipEntity->getFrontendData(['parent']);

        $this->assertEquals($ship->id, $frontendData['id']);
        $this->assertEquals($ship->name->description, $frontendData['name']['en']);

        $parentData = (new ShipGroupEntity($ship->shipGroup))->getFrontendData(['parent']);
        $this->assertEquals($parentData, $frontendData['parent']);
    }
}
