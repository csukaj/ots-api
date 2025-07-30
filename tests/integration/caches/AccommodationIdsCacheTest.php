<?php
namespace Tests\Integration\Caches;

use App\Accommodation;
use App\Caches\AccommodationIdsCache;
use App\Manipulators\OrganizationSetter;
use Tests\TestCase;

class AccommodationIdsCacheTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    public function it_can_load_ids_from_cache()
    {
        $count = Accommodation::count();
        $expected = range(1, $count);
        $actual = (new AccommodationIdsCache())->getValues();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function it_is_updated_when_a_new_hotel_is_added()
    {
        $count = Accommodation::count();
        $org = (new OrganizationSetter([
            'id' => $count + 1,
            'name' => ['en' => 'HotelIdsCacheTest'],
            'type' => 'accommodation',
            'category' => 'Hotel',
            'location_id' => null,
            'properties' => [
                "Discount calculations base" => ["name" => "Discount calculations base", "value" => "rack prices", "categoryId" => 205],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
            ]))->set(true);
        $org->is_active = true;
        $org->save();

        $expected = range(1, $count + 1);
        $actual = (new AccommodationIdsCache())->getValues();
        $this->assertEquals($expected, $actual);
    }
}
