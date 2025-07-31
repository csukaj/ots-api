<?php

namespace Tests\Functional\Controllers;

use App\Caches\AccommodationSearchOptionsCache;
use App\Island;
use App\Organization;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class AccommodationTextSearchFunctionalTest extends TestCase {
    
    static public $setupMode = self::SETUPMODE_ONCE;
    private $responseData;
    private $responseOptionsData;
    
    public function setUp(): void {
        parent::setUp();
        Redis::flushall();
        $this->responseData = $this->assertSuccessfulHttpApiRequest(
            '/accommodation-search/searchable-texts',
            'GET',
            [],
            [],
            true
        );
        $this->responseOptionsData = $this->assertSuccessfulHttpApiRequest(
            '/accommodation-search/search-options',
            'GET',
            [],
            [],
            true
        );
    }
    
    public function tearDown(): void {
        Redis::flushall();
        parent::tearDown();
    }
    
    /**
     * @test
     */
    public function it_can_list_searchable_texts() {
        $this->assertTrue($this->responseData['success']);
        $this->assertNotEmpty($this->responseData['data']);
    }
    
    /**
     * @test
     */
    public function it_contains_all_accommodations() {
        foreach (Organization::where('type_taxonomy_id', '=', Config::get('taxonomies.organization_types.accommodation.id'))->get() as $organization) {
            $actualOrganization = null;
            foreach ($this->responseData['data'] as $responseItem){
                if($organization->name->description == $responseItem['name']['en']){
                    $actualOrganization = $responseItem;
                }
            }
            $this->assertNotEmpty($actualOrganization);
            $this->assertContains($organization->id, $actualOrganization['accommodations']);
            
            foreach ($organization->name->translations as $translation) {
                $languageCode = $translation->language->iso_code;
                $this->assertEquals($translation->description, $actualOrganization['name'][$languageCode]);
            }
        }
    }
    
    /**
     * @test
     */
    public function it_contains_all_search_options() {
        
        $options = (new AccommodationSearchOptionsCache())->getValues();
        $this->assertCount(count($options),$this->responseOptionsData['data']);
        $this->assertEquals(json_decode(json_encode($options), true),$this->responseOptionsData['data']);
        
    }
    
}
