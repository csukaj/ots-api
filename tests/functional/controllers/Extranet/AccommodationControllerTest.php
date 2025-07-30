<?php

namespace Tests\Functional\Controllers\Extranet;

use App\Accommodation;
use App\Entities\AccommodationEntity;
use App\Facades\Config;
use Tests\TestCase;

class AccommodationControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;

    /**
     * @test
     */
    public function it_can_list_accommodations()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/accommodation', 'GET', $token, [], true);

        $expected = AccommodationEntity::getCollection(Accommodation::all(), ['availability_mode', 'galleries', 'availability_update']);
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_list_accommodations_as_advisor()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_advisor')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/accommodation', 'GET', $token);

        $this->assertEquals(count(Accommodation::all()), count($responseData->data));
    }

    /**
     * @test
     */
    public function it_can_list_accommodations_as_manager()
    {
        list($token, $user) = $this->login([Config::get('stylersauth.role_manager')]);
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/accommodation', 'GET', $token);

        $this->assertEquals(count($user->organizations), count($responseData->data));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_get_accommodation_data_when_accommodation_not_channel_managed()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $accommodationId = 1;
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/accommodation/' . $accommodationId, 'GET', $token, [], true);

        $expected = (new AccommodationEntity(Accommodation::findOrFail($accommodationId)))->getFrontendData(['availability_mode', 'galleries', 'availability_update']);
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function it_can_get_accommodation_data_when_accommodation_IS_channel_managed()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $accommodationId = 21;
        $expectedUpdateBefore = (new AccommodationEntity(Accommodation::findOrFail($accommodationId)))
            ->getFrontendData(['availability_update'])['availability_update']['last_updated_at'];
        $responseData = $this->assertSuccessfulHttpApiRequest('/extranet/accommodation/' . $accommodationId, 'GET', $token, [], true);
        $expected = (new AccommodationEntity(Accommodation::findOrFail($accommodationId)))->getFrontendData(['availability_mode', 'galleries', 'availability_update']);
        $this->assertEquals($expected, $responseData['data']);
        $this->assertGreaterThan($expectedUpdateBefore,$responseData['data']['availability_update']['last_updated_at']);
    }
}
