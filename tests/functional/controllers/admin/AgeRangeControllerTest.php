<?php

namespace Tests\Functional\Controllers\Admin;

use App\Organization;
use App\AgeRange;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;
use Modules\Stylerstaxonomy\Manipulators\TaxonomySetter;
use Tests\TestCase;

class AgeRangeControllerTest extends TestCase {

    static public $setupMode = self::SETUPMODE_ALWAYS;
    static public $testMode = self::TESTMODE_CONTROLLER_WRITE;

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_age_ranges() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        
        $ageRanges = Organization::findOrFail(1)->ageRanges()->orderBy('from_age', 'asc')->get();
        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/age-range?age_rangeable_type=App\Organization&age_rangeable_id=1', 'GET', $token);
        $allAgeRangeTx = TaxonomyEntity::getCollection(Taxonomy::findOrFail(Config::get('taxonomies.age_range'))->getChildren(), [ 'translations_with_plurals']);
        $allAgeRangeTx = \json_decode(\json_encode($allAgeRangeTx));
        
        $this->assertTrue($responseData->success);
        $this->assertEquals(count($ageRanges), count($responseData->data));

        foreach ($ageRanges as $ageRange) {
            $ageRangeData = array_shift($responseData->data);

            $this->assertEquals($ageRange->id, $ageRangeData->id);
            $this->assertEquals($ageRange->age_rangeable_id, $ageRangeData->age_rangeable_id);
            $this->assertEquals($ageRange->from_age, $ageRangeData->from_age);
            $this->assertEquals($ageRange->to_age, $ageRangeData->to_age);
            $this->assertEquals($ageRange->name->name, $ageRangeData->name_taxonomy);
            $this->assertEquals($ageRange->name->name, $ageRangeData->taxonomy->name);
            
            $this->assertEquals($ageRange->name->translations->count(), count((array)$ageRangeData->taxonomy->translations));
        }
        $this->assertEquals($allAgeRangeTx,$responseData->age_range_names);

    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_can_show_an_age_range() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        
        $ageRange = Organization::findOrFail(1)->ageRanges[0];
        $responseData = $this->assertSuccessfulHttpApiRequest("/admin/age-range/{$ageRange->id}", 'GET', $token);
        
        $this->assertEquals($ageRange->id, $responseData->data->id);
        $this->assertEquals($ageRange->from_age, $responseData->data->from_age);
        $this->assertEquals($ageRange->to_age, $responseData->data->to_age);
        $this->assertEquals($ageRange->name->name, $responseData->data->name_taxonomy);
        $this->assertEquals($ageRange->age_rangeable_id, $responseData->data->age_rangeable_id);
    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_an_age_range() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        
        $data = [
            'age_rangeable_id' => 3,
            'age_rangeable_type' => Organization::class,
            'from_age' => 0,
            'to_age' => 2,
            'name_taxonomy' => $this->faker->word
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/age-range', 'POST', $token, $data);
        
        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($data['name_taxonomy'], $responseData->data->name_taxonomy);
        $this->assertEquals($data['from_age'], $responseData->data->from_age);
        $this->assertEquals($data['to_age'], $responseData->data->to_age);
        $this->assertEquals($data['age_rangeable_id'], $responseData->data->age_rangeable_id);
    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_an_age_range_with_translations() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        
        $tx = (new TaxonomySetter(['en'=>$this->faker->word,'hu'=> $this->faker->word],null,Config::get('taxonomies.age_range')))->set();
        $txEntity = (new TaxonomyEntity($tx))->getFrontendData(['translations']);
        $data = [
            'age_rangeable_id' => 3,
            'age_rangeable_type' => Organization::class,
            'from_age' => 0,
            'to_age' => 2,
            'name_taxonomy' => $this->faker->word,
            'taxonomy' => $txEntity
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/age-range', 'POST', $token, $data);
        
        $this->assertTrue(!!$responseData->data->id);
        $this->assertEquals($data['taxonomy']['translations']['hu'], $responseData->data->taxonomy->translations->hu);
        $this->assertEquals($data['from_age'], $responseData->data->from_age);
        $this->assertEquals($data['to_age'], $responseData->data->to_age);
        $this->assertEquals($data['age_rangeable_id'], $responseData->data->age_rangeable_id);
    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_can_delete_an_age_range() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        
        $data = [
            'age_rangeable_id' => 3,
            'age_rangeable_type' => Organization::class,
            'from_age' => 0,
            'to_age' => 2,
            'name_taxonomy' => $this->faker->word
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest('/admin/age-range', 'POST', $token, $data);
        
        $this->assertTrue(!!$responseData->data->id);
        $rangeToDel = $responseData->data;
        $responseDelData = $this->assertSuccessfulHttpApiRequest('/admin/age-range/'.$rangeToDel->id, 'DELETE', $token);
        
        $this->assertTrue(!!$responseDelData->data->id);
        $this->assertEquals($rangeToDel->from_age, $responseDelData->data->from_age);
        $this->assertEquals($rangeToDel->to_age, $responseDelData->data->to_age);
        $this->assertEquals($rangeToDel->age_rangeable_id, $responseDelData->data->age_rangeable_id);
    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_can_not_delete_bound_age_range() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);
        
        $rangeToDel = AgeRange::where('age_rangeable_id','=',1)
                ->where('age_rangeable_type', '=', Organization::class)
                ->whereNotNull('to_age')
                ->first();
        list(,,$response) = $this->httpApiRequest('/admin/age-range/'.$rangeToDel->id, 'DELETE', $token, [], true);        
        $response->assertStatus(400);
    }
    
    /**
     * @test
     * @group controller-write
     */
    public function it_cannot_delete_default_age_range() {
        list($token, ) = $this->login([Config::get('stylersauth.role_admin')]);   
        
        $rangeToDel = AgeRange::where('age_rangeable_id','=',1)
                ->where('age_rangeable_type', '=', Organization::class)
                ->whereNull('to_age')
                ->first();
        
        list(,, $response) = $this->httpApiRequest('/admin/age-range/'.$rangeToDel->id, 'DELETE', $token, [], true);
        $response->assertStatus(400);
    }
}
