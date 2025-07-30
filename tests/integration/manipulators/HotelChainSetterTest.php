<?php
namespace Tests\Integration\Manipulators;

use App\Exceptions\UserException;
use App\HotelChain;
use App\Manipulators\OrganizationSetter;
use Tests\TestCase;

class HotelChainSetterTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    function it_can_save_parent_organization()
    {
        $data = ["name" => ['en' => $this->faker->word], 'type' => 'hotel_chain',
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]];

        $pOrg = (new OrganizationSetter($data))->set();
        $this->assertInstanceOf(HotelChain::class, $pOrg);
        $this->assertEquals($data['name']['en'], $pOrg->name->description);
    }

    /**
     * @test
     */
    function it_cant_save_parent_organization_with_invalid_data()
    {

        $data = [];

        $this->expectException(UserException::class);
        (new OrganizationSetter($data))->set();
    }

    /**
     * @test
     */
    function it_cant_save_parent_organization_with_existing_name()
    {

        $data = ["name" => ['en' => $this->faker->word], 'type' => 'hotel_chain',
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]];

        (new OrganizationSetter($data))->set();

        $this->expectException(UserException::class);
        (new OrganizationSetter($data))->set();
    }

    /**
     * @test
     */
    function it_can_update_parent_organization()
    {
        $data = ["name" => ['en' => $this->faker->word], 'type' => 'hotel_chain',
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]];

        $pOrg = (new OrganizationSetter($data))->set();
        $this->assertInstanceOf(HotelChain::class, $pOrg);

        $update = [
            "id" => $pOrg->id,
            "name" => ['en' => $this->faker->word],
            'type' => 'hotel_chain'
        ];

        $updatedHotelChain = (new OrganizationSetter($update))->set();
        $this->assertInstanceOf(HotelChain::class, $pOrg);
        $this->assertEquals($pOrg->id, $updatedHotelChain->id);
        $this->assertEquals($update['name']['en'], $updatedHotelChain->name->description);
    }
}
