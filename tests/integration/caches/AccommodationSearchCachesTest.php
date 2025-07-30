<?php

namespace Tests\Integration\Caches;

use App\Accommodation;
use App\Caches\AccommodationSearchableTextsCache;
use App\Caches\AccommodationSearchOptionsCache;
use App\Entities\AccommodationEntity;
use App\Facades\Config;
use App\Manipulators\OrganizationSetter;
use App\Organization;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class AccommodationSearchCachesTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;

    /**
     * @test
     */
    public function it_can_load_names_from_cache()
    {
        $organizationNames = Accommodation::getNames()->toArray();
        $actual = (new AccommodationSearchableTextsCache())->getValues();
        foreach ($organizationNames as $orgNameItem) {
            $foundCachedName = null;
            foreach ($actual as $cachedHotelName) {
                if ($orgNameItem['description'] == $cachedHotelName->name->{$orgNameItem['language']}) {
                    $foundCachedName = $cachedHotelName;
                }
            }
            $this->assertNotEmpty($foundCachedName);
            $this->assertContains($orgNameItem['id'], $foundCachedName->accommodations);
        }
    }

    /**
     * @test
     */
    public function it_is_updated_when_a_new_hotel_is_added()
    {
        $count = Organization::all()->count();
        $org = (new OrganizationSetter([
            'name' => ['en' => 'HotelSearchableTextsCacheTest'],
            'type' => 'accommodation',
            'category' => 'Hotel',
            'location_id' => null,
            'properties' => [
                "Discount calculations base" => [
                    "name" => "Discount calculations base",
                    "value" => "rack prices",
                    "categoryId" => 205
                ],
                "Merged free nights" => ["name" => "Merged free nights", "value" => "enabled", "categoryId" => 205]
            ]
        ]))->set();
        $org->is_active = true;
        $this->assertTrue($org->save());
        $this->assertCount($count + 1, Organization::all());

        $names = (new AccommodationSearchableTextsCache())->getValues();
        $actual = array_map(function ($v) {
            return $v->name->en;
        }, $names);

        $this->assertContains('HotelSearchableTextsCacheTest', $actual);
    }

    /**
     * @test
     */
    public function it_is_updated_when_a_hotel_name_modified()
    {
        $count = Organization::all()->count();
        $org = (new OrganizationSetter([
            'id' => 1,
            'name' => ['en' => 'HotelSearchableTextsCacheTest2'],
            'type' => 'accommodation',
            'category' => 'Hotel',
            'location_id' => null
        ]))->set();
        $org->is_active = true;
        $this->assertTrue($org->save());
        $this->assertCount($count, Organization::all());

        $names = (new AccommodationSearchableTextsCache())->getValues();
        $actual = array_map(function ($v) {
            return $v->name->en;
        }, $names);
        $this->assertContains('HotelSearchableTextsCacheTest2', $actual);
    }

    /**
     * @test
     */
    public function it_can_load_search_options_from_cache()
    {
        $hardcodedTxIds = AccommodationEntity::getHardcodedSearchoptionTaxonomyIds();
        $searchables = Taxonomy::searchable()->get()->filter(function($item){
            return $item->parent->parent->id == Config::getOrFail('taxonomies.organization_properties.category');
        })
        ->filter(function($item) use ($hardcodedTxIds){
            return in_array($item->id, $hardcodedTxIds); // @todo @ivan @20190128 @ @lgabor @20190410 @see AccommodationSearchOptionsCache::getFreshSearchOptions() ~ line 51
        });
        $actual = (new AccommodationSearchOptionsCache())->getValues();

        $actualCount = 0;
        foreach ($actual as $category) {
            $actualCount += count($category->items);
        }
        $this->assertEquals(count($searchables),$actualCount);

        foreach ($searchables as $searchable) {
            $found = false;
            foreach ($actual as $category) {
                if (!$category->name->en || $searchable->parent->name == $category->name->en) {
                    foreach ($category->items as $item) {
                        if ($searchable->name == $item->name->en) {
                            $found = true;
                        }
                    }
                }
            }
            $this->assertTrue($found);
        }
    }

    /**
     * @test
     */
    public function it_is_updated_when_a_classification_made_searchable()
    {
        $this->markTestIncomplete();
    }

}
