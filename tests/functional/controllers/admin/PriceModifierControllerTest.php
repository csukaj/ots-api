<?php

namespace Tests\Functional\Controllers\Admin;

use App\DateRange;
use App\Entities\PriceModifierEntity;
use App\Facades\Config;
use App\Organization;
use App\PriceModifier;
use Tests\TestCase;

class PriceModifierControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    private function prepare_models_and_entity()
    {
        $organization = Organization::findOrFail(1);
        return [$organization];
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_price_modifiers()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($organization) = $this->prepare_models_and_entity();

        $priceModifierIds = PriceModifier::getModelPriceModifierIds(Organization::class, $organization->id);
        $expected = PriceModifierEntity::getCollection(PriceModifier::orderBy('priority')->find($priceModifierIds),
            ['properties']);

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/price-modifier?price_modifiable_type=' . Organization::class . '&price_modifiable_id=' . $organization->id,
            'GET', $token, [], true);

        $this->assertCount(count($expected), $responseData['data']);
        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_get_a_price_modifier()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($organization) = $this->prepare_models_and_entity();

        $priceModifierIds = PriceModifier::getModelPriceModifierIds(Organization::class, $organization->id);
        $expected = $priceModifierIds[0];

        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price-modifier/{$expected}", 'GET', $token, [],
            true);
        $this->assertEquals((new PriceModifierEntity(PriceModifier::findOrFail($expected)))->getFrontendData(['properties']),
            $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_a_new_price_modifier()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($organization, ,) = $this->prepare_models_and_entity();

        $dateRange = DateRange::priceModifier()->forDateRangeable(Organization::class, $organization->id)->first();

        $data = [
            'organization_id' => $organization->id,
            'name' => ['en' => $this->faker->word],
            'description' => ['en' => $this->faker->word],
            'modifier_type' => 'Discount (non-mandatory, visible)',
            'condition' => 'long_stay',
            'offer' => 'percentage',
            'is_active' => true,
            'priority' => 2,
            'date_ranges' => [
                [
                    'id' => $dateRange->id,
                    'date_rangeable_type' => Organization::class,
                    'date_rangeable_id' => $organization->id,
                    'name' => null,
                    'from_date' => substr($dateRange->from_time, 0, 10),
                    'to_date' => substr($dateRange->to_time, 0, 10),
                    'type' => 'price_modifier',
                ],
            ],
            'promo_code' => $this->faker->word,
            'condition_properties' => ['x'],
            'offer_properties' => ['y'],
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/price-modifier', 'POST', $token, $data);

        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($data['name']['en'], $responseData->data->name->en);
        $this->assertEquals($data['is_active'], $responseData->data->is_active);

    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_edit_a_price_modifier()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($organization) = $this->prepare_models_and_entity();

        list($priceModifierId) = PriceModifier::getModelPriceModifierIds(Organization::class, $organization->id);

        $data = (new PriceModifierEntity(PriceModifier::findOrFail($priceModifierId)))->getFrontendData(['properties']);
        $data['name']['en'] = $this->faker->word;
        $data['is_annual'] = true;
        $data['description'] = ['en' => $this->faker->word]; //needed because empty values cleared indexes in json encoding
        $data['condition_properties'] = ['x'];//needed because empty values cleared indexes in json encoding
        $data['offer_properties'] = ['y'];//needed because empty values cleared indexes in json encoding

        $this->assertSuccessfulHttpApiRequest("/admin/price-modifier", 'POST', $token, $data, true);

        $data['condition_properties'] = [
            'metas' => [['name' => 'minimum_nights', 'value' => '2']],
            'classifications' => []
        ];//needed because empty values cleared indexes in json encoding
        $data['offer_properties'] = [
            'metas' => [],
            'classifications' => []
        ];//needed because empty values cleared indexes in json encoding
        $data['modifier_type'] = 'Rule (mandatory, visible)';
        $responseUpdData = $this->assertSuccessfulHttpApiRequest("/admin/price-modifier", 'POST', $token, $data,
            true);

        $this->assertEquals($data, $responseUpdData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_delete_a_price_modifier()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($organization,) = $this->prepare_models_and_entity();

        list($priceModifierId) = PriceModifier::getModelPriceModifierIds(Organization::class, $organization->id);

        $this->assertSuccessfulHttpApiRequest("/admin/price-modifier/{$priceModifierId}", 'DELETE', $token);

        $this->assertNotEmpty(PriceModifier::onlyTrashed()->find($priceModifierId));
    }

}
