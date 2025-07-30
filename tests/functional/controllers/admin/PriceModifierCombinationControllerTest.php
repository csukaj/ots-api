<?php
namespace Tests\Functional\Controllers\Admin;

use App\PriceModifier;
use App\PriceModifierCombination;
use App\Entities\PriceModifierCombinationEntity;
use App\Entities\PriceModifierEntity;
use App\Facades\Config;
use App\Organization;
use Tests\TestCase;

class PriceModifierCombinationControllerTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_combinations()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $organization = Organization::findOrFail(1);

        $priceModifierIds = PriceModifier::getModelPriceModifierIds(Organization::class, $organization->id);

        $combinations = PriceModifierCombinationEntity::getCollection(PriceModifierCombination::getForPriceModifiers($priceModifierIds));
        $priceModifiers = PriceModifierEntity::getCollection(PriceModifier::orderBy('priority')->find($priceModifierIds));


        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/price-modifier-combinations?price_modifiable_type=' . Organization::class . '&price_modifiable_id=' . $organization->id, 'GET', $token, [], true);

        $this->assertEquals($combinations, $responseData['data']);
        $this->assertEquals($priceModifiers, $responseData['price_modifiers']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_show_a_combination()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $combination = PriceModifierCombination::firstOrFail();
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/price-modifier-combinations/{$combination->id}", 'GET', $token);

        $this->assertEquals($combination->id, $responseData->data->id);
        $this->assertEquals($combination->first_price_modifier_id, $responseData->data->first_price_modifier_id);
        $this->assertEquals($combination->second_price_modifier_id, $responseData->data->second_price_modifier_id);
    }

    /**
     * @test
     * @group controller-write
     * 
     * //it fails because some way empty arrays get filtered...
     * 
     */
    public function it_can_store_combinations()
    {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);

        $organization = Organization::findOrFail(1);

        $fakeProperties = ["classifications" => ['a'], "metas" => ['a']];
        $fakeDesc = ['en' => $this->faker->word];

        $priceModifierIds = PriceModifier::getModelPriceModifierIds(Organization::class, $organization->id);

        $priceModifiers = PriceModifierEntity::getCollection(PriceModifier::orderBy('priority')->find($priceModifierIds));
        $priceModifierCombinations = PriceModifierCombinationEntity::getCollection(PriceModifierCombination::getForPriceModifiers($priceModifierIds));
        unset($priceModifierCombinations[count($priceModifierCombinations) - 1]);

        //this needed because empty arrays removed from request...
        $rqDiscounts = [];
        foreach ($priceModifiers as $d) {
            $d['condition_properties'] = $fakeProperties;
            $d['offer_properties'] = $fakeProperties;
            $d['description'] = $fakeDesc;
            $rqDiscounts[] = $d;
        }

        $data = ["priceModifiers" => $rqDiscounts, "combinations" => $priceModifierCombinations];
        $expected = ["success" => true, "data" => $priceModifierCombinations, "price_modifiers" => $priceModifiers];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/price-modifier-combinations?price_modifiable_type=' . Organization::class . '&price_modifiable_id=' . $organization->id, 'POST', $token, $data, true);

        $this->assertEquals($expected, $responseData);
    }
}
