<?php
namespace Tests\Integration\Manipulators;

use App\Entities\LocationEntity;
use App\Entities\PoiEntity;
use App\Location;
use App\Manipulators\PoiSetter;
use App\Poi;
use Tests\TestCase;

class PoiSetterTest extends TestCase
{

    /**
     * @test
     */
    function it_can_set_a_new_poi()
    {


        $data = [
            'type' => 'Port',
            'name' => ['en' => $this->faker->word],
            'description' => ['en' => $this->faker->sentence],
            'location' => (new LocationEntity(Location::first()))->getFrontendData(['admin'])
        ];

        $poi = (new PoiSetter($data))->set();
        $this->assertInstanceOf(Poi::class, $poi);
        $this->assertNotEmpty($poi->id);
        $this->assertEquals($data['type'], $poi->type->name);
        $this->assertEquals($data['name']['en'], $poi->name->description);
        $this->assertEquals($data['description']['en'], $poi->description->description);
        $this->assertEquals($data['location']['latitude'], $poi->location->latitude);
        $this->assertEquals($data['location']['longitude'], $poi->location->longitude);
        ;
    }

    /**
     * @test 
     */
    function it_can_update_a_poi()
    {

        $data = (new PoiEntity(Poi::first()))->getFrontendData();
        $data['name']['en'] = $this->faker->word;

        $poi = (new PoiSetter($data))->set();
        $this->assertInstanceOf(Poi::class, $poi);
        $this->assertEquals($data['id'], $poi->id);
        $poiArray = (new PoiEntity($poi))->getFrontendData();
        unset($poiArray['updated_at']);
        $this->assertEquals($data, $poiArray);
    }

}
