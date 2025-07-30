<?php

namespace Modules\Stylerstaxonomy\Tests\Functional;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DescriptionFuncTest extends TestCase {

    private $adminToken;
    private $userToken;
    
    public function setUp() {
        parent::setUp();
        list($this->adminToken,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($this->userToken,) = $this->login([Config::get('stylersauth.role_user')]);
    }
    
    /**
     * @test
     */
    public function it_can_be_created() {
        $this->httpApiRequest('/stylerstaxonomy/description', 'POST', $this->userToken, ['description' => $description = $this->faker->word]);

        list(, $responseData) = $this->httpApiRequest('/stylerstaxonomy/description', 'POST', $this->adminToken, ['description' => $description = $this->faker->word]);
        $this->assertTrue($responseData->success);
        $this->assertEquals($description, $responseData->data->description);
    }

    /**
     * @test
     */
    public function it_can_be_updated() {
        list(, $responseData) = $this->httpApiRequest('/stylerstaxonomy/description', 'POST', $this->adminToken, ['description' => $description = $this->faker->word]);
        
        list(, , $response) = $this->httpApiRequest("/stylerstaxonomy/description/{$responseData->data->id}", 'PUT', $this->userToken, ['description' => $this->faker->word]);
        $response->assertStatus(403);
        
        list(, $responseData3) = $this->httpApiRequest("/stylerstaxonomy/description/{$responseData->data->id}", 'PUT', $this->adminToken, ['description' => $newName = $this->faker->word]);

        $this->assertTrue($responseData3->success);
        $this->assertEquals($newName, $responseData3->data->description);
    }
        
    /**
     * @test
     */
    public function it_can_be_shown() {
        list(, $responseDataCreate) = $this->httpApiRequest('/stylerstaxonomy/description', 'POST', $this->adminToken, ['description' => $description = $this->faker->word]);
                
        list(, $responseDataShow) = $this->httpApiRequest("/stylerstaxonomy/description/{$responseDataCreate->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataShow->success);
        $this->assertEquals($responseDataCreate->data->id, $responseDataShow->data->id);
        $this->assertEquals($description, $responseDataShow->data->description);
    }

    /**
     * @test
     */
    public function it_can_be_deleted() {
        list(, $responseDataCreate) = $this->httpApiRequest('/stylerstaxonomy/description', 'POST', $this->adminToken, ['description' => $description = $this->faker->word]);
                
        list(, $responseDataShow) = $this->httpApiRequest("/stylerstaxonomy/description/{$responseDataCreate->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataShow->success);
        $this->assertEquals($responseDataCreate->data->id, $responseDataShow->data->id);
        
        list(, $responseDataDelete) = $this->httpApiRequest("/stylerstaxonomy/description/{$responseDataCreate->data->id}", 'DELETE', $this->adminToken);
        $this->assertTrue($responseDataDelete->success);
        $this->assertEquals($responseDataCreate->data->id, $responseDataDelete->data->id);
        $this->assertNotNull($responseDataDelete->data->deleted_at);
        
        list(,, $response) = $this->httpApiRequest("/stylerstaxonomy/description/{$responseDataCreate->data->id}", 'GET', $this->userToken);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_can_be_listed() {
        list(, $responseDataCreate1) = $this->httpApiRequest('/stylerstaxonomy/description', 'POST', $this->adminToken, ['description' => $description1 = $this->faker->word]);
        list(, $responseDataCreate2) = $this->httpApiRequest('/stylerstaxonomy/description', 'POST', $this->adminToken, ['description' => $description2 = $this->faker->word]);
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/description", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreate1, $responseDataCreate2) {
                return in_array($element->id, [$responseDataCreate1->data->id, $responseDataCreate2->data->id]);
            }
        );
        $this->assertEquals(2, count($foundObjects));
    }

}
