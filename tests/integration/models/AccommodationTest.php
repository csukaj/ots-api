<?php

namespace Tests\Integration\Models;

use App\Accommodation;
use App\Device;
use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Description;
use Tests\TestCase;

class AccommodationTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @param null $id
     * @return Accommodation
     */
    private function prepare_an_organization_model($id = null): Accommodation
    {
        if($id){
            return Accommodation::findOrFail( $id);
        }
        $description = new Description(['description' => $descDesc = $this->faker->word]);
        $this->assertTrue($description->save());

        $accommodation = new Accommodation();
        $accommodation->name_description_id = $description->id;
        $accommodation->type_taxonomy_id =  Config::getOrFail('taxonomies.organization_types.accommodation.id');
        $accommodation->save();

        $device = new Device();

        return $accommodation;
    }


    /**
     * @test
     * @throws \Exception
     */
    function it_can_getChannelManagerId()
    {
        $accommodation = $this->prepare_an_organization_model(21);
        $expected = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.channel_manager.elements.Hotel Link Solutions');
        $this->assertEquals($expected, $accommodation->getChannelManagerId());
        $accommodation = $this->prepare_an_organization_model();
        $this->assertEmpty($accommodation->getChannelManagerId());
    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_getChannelManagerHotelId()
    {
        $accommodation = $this->prepare_an_organization_model(21);

        $this->assertEquals('5994c2db-cd76-401c-ba2e-e178ae118a8d', $accommodation->getChannelManagerHotelId());
        $accommodation = $this->prepare_an_organization_model();
        $this->assertEmpty($accommodation->getChannelManagerHotelId());
    }

    /**
     * @test
     * @throws \Exception
     */
    function it_can_getHotelAuthenticationChannelKey()
    {
        $accommodation = $this->prepare_an_organization_model(21);

        $this->assertEquals('74dd9b27c6d1fb5fb1289fae19878cac', $accommodation->getHotelAuthenticationChannelKey());
        $accommodation = $this->prepare_an_organization_model();
        $this->assertEmpty($accommodation->getHotelAuthenticationChannelKey());
    }
}
