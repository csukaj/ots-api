<?php
namespace Tests\Functional\Controllers;

use Tests\TestCase;

class CartUpdateFunctionalTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    const TEST_DATA_DIR = __DIR__.'/CartUpdateFunctionalTestData/';

    protected function runWithJsons($requestJsonFile, $responseJsonFile)
    {
        $requestBody = json_decode(file_get_contents(self::TEST_DATA_DIR . $requestJsonFile), true);
        $expectedResponseBody = json_decode(file_get_contents(self::TEST_DATA_DIR . $responseJsonFile), true);

        $responseData = $this->assertSuccessfulHttpApiRequest('/cart/update', 'POST', [], $requestBody, true);

        $this->assertEquals(
            $expectedResponseBody['data'][0]['productableModel']['priceSearchData'], $responseData['data'][0]['productableModel']['priceSearchData']
        );

        return $responseData;
    }

    /**
     * @test
     */
    public function it_can_update_the_cart_with_chain_price_modifier()
    {
        $this->runWithJsons(
            'it_can_update_the_cart_with_chain_discount_request.json',
            'it_can_update_the_cart_with_chain_discount_response.json'
        );
    }

    /**
     * @test
     */
    public function it_can_be_overbooked()
    {
        $this->runWithJsons(
            'it_can_be_overbooked_request.json', 'it_can_be_overbooked_response.json'
        );
    }

    /**
     * @test
     */
    public function it_can_update_the_cart_with_family_combo_price_modifier()
    {
        $this->runWithJsons(
            'family_combo_request.json', 'family_combo_response.json'
        );
    }

    /**
     * @test
     */
    public function it_can_update_the_cart_with_family_combo_price_modifier_when_child_age_is_in_adult_range()
    {
        $this->runWithJsons(
            'family_combo_request_big_child.json', 'family_combo_response_big_child.json'
        );
    }

    /**
     * @test
     */
    public function it_can_update_the_cart_with_suite_reservation_price_modifier()
    {
        $this->runWithJsons(
            'suite_reservation_request.json', 'suite_reservation_response.json'
        );
    }

    /**
     * @test
     */
    public function it_can_update_the_cart_with_group_price_modifier()
    {
        $this->runWithJsons(
            'group_discount_request.json', 'group_discount_response.json'
        );
    }
}
