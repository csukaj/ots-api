<?php
namespace Tests\Integration\Entities;

use App\Entities\AccommodationEntity;
use App\Entities\HotelChainEntity;
use App\HotelChain;
use Tests\TestCase;

class HotelChainEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity(): array
    {
        $hotelChain = HotelChain::all()->first();
        return [$hotelChain, (new HotelChainEntity($hotelChain))];
    }

    /**
     * @test
     */
    function it_can_present_organization_data()
    {
        list($hotelChain, $hotelChainEntity) = $this->prepare_model_and_entity();
        $frontendData = $hotelChainEntity->getFrontendData();

        $this->assertEquals($hotelChain->id, $frontendData['id']);
        $this->assertEquals($hotelChain->name->description, $frontendData['name']['en']);
    }

    /**
     * @test
     */
    function it_can_present_organization_data_with_children()
    {
        list($hotelChain, $hotelChainEntity) = $this->prepare_model_and_entity();
        $frontendData = $hotelChainEntity->getFrontendData(['accommodations']);

        $this->assertEquals($hotelChain->id, $frontendData['id']);
        $this->assertEquals($hotelChain->name->description, $frontendData['name']['en']);
        $this->assertEquals(count($hotelChain->children), count($frontendData['accommodations']));
        foreach ($hotelChain->accommodations as $idx => $value) {
            $this->assertEquals((new AccommodationEntity($value))->getFrontendData(), $frontendData['accommodations'][$idx]);
        }
    }
}
