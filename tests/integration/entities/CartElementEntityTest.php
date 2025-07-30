<?php
namespace Tests\Integration\Entities;

use App\Entities\CartElementEntity;
use Tests\TestCase;

class CartElementEntityTest extends TestCase
{

    private function prepare_models_and_entity()
    {
        return [new CartElementEntity([])];
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
}
