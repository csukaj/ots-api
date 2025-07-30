<?php

namespace Tests\Functional\Controllers\Admin;

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
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/accommodation', 'GET', $token);

        $this->assertEquals(count(Accommodation::all()), count($responseData->data));
    }

    /**
     * @test
     */
    public function it_can_show_an_accommodation()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/accommodation/1', 'GET', $token, [], true);

        $this->assertEquals((new AccommodationEntity(Accommodation::find(1)))->getFrontendData(['descriptions', 'location', 'availability_mode', 'admin_properties', 'galleries', 'supplier']), $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_store_an_accommodation()
    {
        $this->markTestIncomplete('Need to implement');
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/accommodation/1', 'GET', $token, [], true);

        $this->assertEquals((new AccommodationEntity(Accommodation::find(1)))->getFrontendData(['descriptions', 'location', 'availability_mode', 'admin_properties', 'galleries']), $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_update_an_accommodation()
    {
        $this->markTestIncomplete('Need to implement');

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/accommodation/1', 'GET', $token, [], true);

        $this->assertEquals((new AccommodationEntity(Accommodation::find(1)))->getFrontendData(['descriptions', 'location', 'availability_mode', 'admin_properties', 'galleries']), $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_delete_an_accommodation()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/accommodation/1', 'DELETE', $token, [], true);
        $this->assertNotEmpty(Accommodation::onlyTrashed()->findOrFail(1));
        $this->assertEquals([], $responseData['data']);
    }

    /**
     * @test
     */
    public function it_can_show_an_overview_of_an_accommodation()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/accommodation/overview/1', 'GET', $token, [], true);

        $this->assertEquals((new AccommodationEntity(Accommodation::find(1)))->getFrontendData(['properties', 'supplier', 'devices', 'date_ranges', 'device_amount', 'prices', 'device_margin', 'pricing']), $responseData['data']);
    }
}
