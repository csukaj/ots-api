<?php
namespace Tests\Integration\Entities;

use App\Entities\LocationEntity;
use App\Entities\PoiEntity;
use App\Poi;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class PoiEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    private function prepare_model_and_entity(): array
    {
        $poi = Poi::all()->first();
        return [$poi, (new PoiEntity($poi))];
    }

    /**
     * @test
     */
    function it_can_present_poi_data()
    {
        list($poi, $poiEntity) = $this->prepare_model_and_entity();
        $frontendData = $poiEntity->getFrontendData();

        $expected = [
            'id' => $poi->id,
            'type' => $poi->type->name,
            'name' => (new DescriptionEntity($poi->name))->getFrontendData(),
            'description' => (new DescriptionEntity($poi->description))->getFrontendData(),
            'location' => (new LocationEntity($poi->location))->getFrontendData(['admin'])
        ];
        
        $this->assertEquals($expected, $frontendData);
    }

    
}