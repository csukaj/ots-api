<?php

namespace Modules\Stylerstaxonomy\Tests\Functional;

use App\Exceptions\UserException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Tests\TestCase;

class TaxonomyFuncTest extends TestCase {

    private $adminToken;
    private $userToken;
    
    public function setUp(): void {
        parent::setUp();
        list($this->adminToken,) = $this->login([Config::get('stylersauth.role_admin')]);
        list($this->userToken,) = $this->login([Config::get('stylersauth.role_user')]);
    }
    
    /**
     * @test
     */
    public function it_can_be_created() {
        list(, , $response) = $this->httpApiRequest('/stylerstaxonomy/taxonomy', 'POST', $this->userToken, ['name' => $name = $this->faker->word]);
        $response->assertStatus(403);

        list(, $responseData) = $this->httpApiRequest('/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name = $this->faker->word]);
        $this->assertTrue($responseData->success);
        $this->assertEquals($name, $responseData->data->name);
    }

    /**
     * @test
     */
    public function it_can_be_updated() {
        list(, $responseData) = $this->httpApiRequest('/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name = $this->faker->word]);
        
        list(, , $response) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$responseData->data->id}", 'PUT', $this->userToken, ['name' => $this->faker->word]);
        $response->assertStatus(403);
        
        list(, $responseData) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$responseData->data->id}", 'PUT', $this->adminToken, ['name' => $newName = $this->faker->word]);

        $this->assertTrue($responseData->success);
        $this->assertEquals($newName, $responseData->data->name);
    }
        
    /**
     * @test
     */
    public function it_can_be_shown() {
        list(, $responseDataCreate) = $this->httpApiRequest('/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name = $this->faker->word]);
                
        list(, $responseDataShow) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$responseDataCreate->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataShow->success);
        $this->assertEquals($responseDataCreate->data->id, $responseDataShow->data->id);
        $this->assertEquals($name, $responseDataShow->data->name);
    }

    /**
     * @test
     */
    public function it_can_be_deleted() {
        list(, $responseDataCreate) = $this->httpApiRequest('/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name = $this->faker->word]);
                
        list(, $responseDataShow) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$responseDataCreate->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataShow->success);
        $this->assertEquals($responseDataCreate->data->id, $responseDataShow->data->id);
        
        list(, $responseDataDelete) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$responseDataCreate->data->id}", 'DELETE', $this->adminToken);
        $this->assertTrue($responseDataDelete->success);
        $this->assertEquals($responseDataCreate->data->id, $responseDataDelete->data->id);
        $this->assertNotNull($responseDataDelete->data->deleted_at);
        
        list(,, $response) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$responseDataCreate->data->id}", 'GET', $this->userToken);
        $response->assertStatus(404);
    }

    /**
     * @test
     */
    public function it_can_not_be_deleted_when_active_relation_exists() {
        $firstTxId = Taxonomy::first()->id;
        list(, $responseDataDelete, $response) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$firstTxId}", 'DELETE', $this->adminToken);
        $response->assertStatus(400);
        $this->assertFalse($responseDataDelete->success);
        $this->assertContains('You can\'t delete a taxonomy with active relation!',$responseDataDelete->error);
    }

    /**
     * @test
     */
    public function it_can_be_listed() {
        list(, $responseDataCreate1) = $this->httpApiRequest('/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name1 = $this->faker->word]);
        list(, $responseDataCreate2) = $this->httpApiRequest('/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name2 = $this->faker->word]);
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/taxonomy", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreate1, $responseDataCreate2) {
                return in_array($element->id, [$responseDataCreate1->data->id, $responseDataCreate2->data->id]);
            }
        );
        $this->assertEquals(2, count($foundObjects));
    }

    /**
     * @test
     */
    public function it_can_have_descendants() {
        list(, $responseDataCreateParent) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        list(, $responseDataCreateChild1) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name1 = $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateChild2) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name2 = $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateGrandChild) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name2 = $this->faker->word, 'parent_id' => $responseDataCreateChild2->data->id]
        );
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/descendants/{$responseDataCreateParent->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreateChild1, $responseDataCreateChild2, $responseDataCreateGrandChild) {
                return in_array($element->id, [$responseDataCreateChild1->data->id, $responseDataCreateChild2->data->id, $responseDataCreateGrandChild->data->id]);
            }
        );
        $this->assertEquals(3, count($responseDataList->data));
        $this->assertEquals(3, count($foundObjects));
        
        list(, $responseDataGet) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$responseDataCreateParent->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataGet->data->has_descendants);
    }
    
    /**
     * @test
     */
    public function children_can_be_queried() {
        list(, $responseDataCreateParent) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        list(, $responseDataCreateChild1) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name1 = $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateChild2) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name2 = $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateGrandChild) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name2 = $this->faker->word, 'parent_id' => $responseDataCreateChild2->data->id]
        );
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/children/{$responseDataCreateParent->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreateChild1, $responseDataCreateChild2) {
                return in_array($element->id, [$responseDataCreateChild1->data->id, $responseDataCreateChild2->data->id]);
            }
        );
        $this->assertEquals(2, count($responseDataList->data));
        $this->assertEquals(2, count($foundObjects));
        
        list(, $responseDataGet) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/{$responseDataCreateParent->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataGet->data->has_descendants);
    }
    
    /**
     * @test
     */
    public function roots_can_be_queried() {
        list(, $responseDataCreate1) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        list(, $responseDataCreate2) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/children/", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreate1, $responseDataCreate2) {
                return in_array($element->id, [$responseDataCreate1->data->id, $responseDataCreate2->data->id]);
            }
        );
        $this->assertEquals(2, count($foundObjects));
    }

    /**
     * @test
     */
    public function it_can_list_leaves() {
        list(, $responseDataCreateParent) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        list(, $responseDataCreateChild1) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateChild2) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateGrandChild) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateChild2->data->id]
        );
        
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/leaves/{$responseDataCreateParent->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreateChild1, $responseDataCreateGrandChild) {
                return in_array($element->id, [$responseDataCreateChild1->data->id, $responseDataCreateGrandChild->data->id]);
            }
        );
        $this->assertEquals(2, count($responseDataList->data));
        $this->assertEquals(2, count($foundObjects));
    }
    
    /**
     * @test
     */
    public function it_can_have_ancestors() {
        list(, $responseDataCreateParent) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        list(, $responseDataCreateChild) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateGrandChild) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateChild->data->id]
        );
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/ancestors/{$responseDataCreateGrandChild->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreateParent, $responseDataCreateChild) {
                return in_array($element->id, [$responseDataCreateParent->data->id, $responseDataCreateChild->data->id]);
            }
        );
        $this->assertEquals(2, count($responseDataList->data));
        $this->assertEquals(2, count($foundObjects));
    }
    
    /**
     * @test
     */
    public function ancestors_and_self_can_be_queried() {
        list(, $responseDataCreateParent) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        list(, $responseDataCreateChild) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateGrandChild) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateChild->data->id]
        );
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/ancestors-and-self/{$responseDataCreateGrandChild->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreateParent, $responseDataCreateChild, $responseDataCreateGrandChild) {
                return in_array($element->id, [$responseDataCreateParent->data->id, $responseDataCreateChild->data->id, $responseDataCreateGrandChild->data->id]);
            }
        );
        $this->assertEquals(3, count($responseDataList->data));
        $this->assertEquals(3, count($foundObjects));
    }
    
    /**
     * @test
     */
    public function it_can_have_siblings() {
        list(, $responseDataCreateParent) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        list(, $responseDataCreateChild1) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateChild2) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreateChild3) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        
        list(, $responseDataList) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/siblings/{$responseDataCreateChild1->data->id}", 'GET', $this->userToken);
        $this->assertTrue($responseDataList->success);
        
        $foundObjects = array_filter(
            $responseDataList->data,
            function ($element) use ($responseDataCreateChild2, $responseDataCreateChild3) {
                return in_array($element->id, [$responseDataCreateChild2->data->id, $responseDataCreateChild3->data->id]);
            }
        );
        $this->assertEquals(2, count($responseDataList->data));
        $this->assertEquals(2, count($foundObjects));
    }
    
    /**
     * @test
     */
    public function it_can_be_reprioritized() {
        list(, $responseDataCreateParent) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $this->faker->word]
        );
        list(, $responseDataCreate1) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name = $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreate2) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name = $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        list(, $responseDataCreate3) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy', 'POST', $this->adminToken, ['name' => $name = $this->faker->word, 'parent_id' => $responseDataCreateParent->data->id]
        );
        
        list(, $responseDataListBefore) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/children/{$responseDataCreateParent->data->id}", 'GET', $this->userToken);
        $this->assertEquals($responseDataCreate1->data->id, $responseDataListBefore->data[0]->id);
        $this->assertEquals($responseDataCreate2->data->id, $responseDataListBefore->data[1]->id);
        $this->assertEquals($responseDataCreate3->data->id, $responseDataListBefore->data[2]->id);
        
        list(, $responseDataPrioritize) = $this->httpApiRequest(
            '/stylerstaxonomy/taxonomy/priorities', 'PUT', $this->adminToken, ['taxonomy_ids' => [$responseDataCreate2->data->id, $responseDataCreate1->data->id, $responseDataCreate3->data->id]]
        );
        $this->assertTrue($responseDataPrioritize->success);
        $this->assertEquals($responseDataCreate2->data->id, $responseDataPrioritize->data[0]->id);
        $this->assertEquals($responseDataCreate1->data->id, $responseDataPrioritize->data[1]->id);
        $this->assertEquals($responseDataCreate3->data->id, $responseDataPrioritize->data[2]->id);
        
        list(, $responseDataListAfter) = $this->httpApiRequest("/stylerstaxonomy/taxonomy/children/{$responseDataCreateParent->data->id}", 'GET', $this->userToken);
        $this->assertEquals($responseDataCreate2->data->id, $responseDataListAfter->data[0]->id);
        $this->assertEquals($responseDataCreate1->data->id, $responseDataListAfter->data[1]->id);
        $this->assertEquals($responseDataCreate3->data->id, $responseDataListAfter->data[2]->id);
    }
}
