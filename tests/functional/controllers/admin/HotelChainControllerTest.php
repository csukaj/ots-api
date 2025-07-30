<?php

namespace Tests\Functional\Controllers\Admin;

use App\Entities\HotelChainEntity;
use App\Facades\Config;
use App\HotelChain;
use Tests\TestCase;

class HotelChainControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    private function prepare_models_and_entity(): array
    {
        $pOrg = HotelChain::all()->first();
        return [$pOrg, (new HotelChainEntity($pOrg))];
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_hotel_chains()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/hotel-chain', 'GET', $token, [], true);

        $allHotelChain = HotelChainEntity::getCollection(HotelChain::all());

        $this->assertEquals(count($allHotelChain), count($responseData['data']));
        for ($i = 0; $i < count($responseData['data']); $i++) {
            $actual = $responseData['data'][$i];
            $expected = $allHotelChain[$i];
            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_a_hotel_chain()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($pOrg, $pOrgEntity) = $this->prepare_models_and_entity();

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/hotel-chain/{$pOrg->id}", 'GET', $token, [], true);
        $this->assertEquals($pOrgEntity->getFrontendData(), $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_a_new_hotel_chain()
    {
        list($token, $user) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = ["name" => ['en' => $this->faker->word], 'type' => 'hotel_chain',
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/hotel-chain', 'POST', $token, $data);
        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals((object)$data['name'], $responseData->data->name);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_edit_a_hotel_chain()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($pOrg,) = $this->prepare_models_and_entity();

        $data = [
            "id" => $pOrg->id,
            "name" => ['en' => $this->faker->word],
            'type' => 'hotel_chain'
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/hotel-chain/{$pOrg->id}", 'PUT', $token,
            $data);

        $this->assertEquals($pOrg->id, $responseData->data->id);
        $this->assertEquals((object)$data['name'], $responseData->data->name);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_delete_a_hotel_chain()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = ["name" => ['en' => $this->faker->word], 'type' => 'hotel_chain',
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]];

        $createResponseData = $this->assertSuccessfulHttpApiRequest('/admin/hotel-chain', 'POST', $token,
            $data);

        $this->assertTrue(!!$createResponseData->data->id);
        $id = $createResponseData->data->id;

        $this->assertSuccessfulHttpApiRequest("/admin/hotel-chain/{$id}", 'DELETE', $token);

        $this->assertNotEmpty(HotelChain::onlyTrashed()->find($id));
    }
}
