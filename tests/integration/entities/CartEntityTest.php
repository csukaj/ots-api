<?php
namespace Tests\Integration\Entities;

use App\Entities\CartEntity;
use Tests\TestCase;

class CartEntityTest extends TestCase
{

    private function prepare_models_and_entity()
    {
        return [new CartEntity([])];
    }

    /**
     * @test
     */
    public function it_can_create_itself()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function it_can_update_itself()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function it_can_getCartSummary()
    {
        $this->markTestIncomplete();
        $expected = [
            'organization_id',
            'device_id',
            'device_name',
            'meal_plan',
            'interval',
            'amount',
            'order_itemable_index',
            'usage_request'
        ];
        list($cartEntity) = $this->prepare_models_and_entity();
        foreach ($cartEntity->getCartSummary()['elements'] as $element) {
            $this->assertEquals($expected, array_keys($element));
        }
    }
}
