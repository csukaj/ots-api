<?php

namespace Tests\Functional\Controllers\Admin;

use App\Entities\ReviewEntity;
use App\Entities\UserEntity;
use App\Organization;
use App\Review;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{

    private $organization;
    private $reviews;
    private $_url = '/admin/review';

    public function setUp(): void
    {
        parent::setUp();
        $this->organization = factory(Organization::class, 'accommodation')->create();
        $this->reviews = factory(Review::class, 5)->create(['review_subject_id' => $this->organization->id]);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_list_reviews()
    {
        $expected = ReviewEntity::getCollection($this->reviews, ['admin']);
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest(
            $this->_url . '?review_subject_type=App\Organization&review_subject_id=' . $this->organization->id, 'GET',
            $token, [], true
        );

        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_show_an_review()
    {
        $review = $this->reviews->first();
        $expected = (new ReviewEntity($review))->getFrontendData(['admin']);

        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);
        $responseData = $this->assertSuccessfulHttpApiRequest(
            $this->_url . '/' . $review->id, 'GET', $token, [], true
        );

        $this->assertEquals($expected, $responseData['data']);
    }

    /**
     * @test
     * @group controller-write
     */
    public function it_can_store_an_review()
    {
        list($token, $user) = $this->login([Config::get('stylersauth.role_admin')]);

        $data = [
            'review_subject_type' => get_class($this->organization),
            'review_subject_id' => $this->organization->id,
            'description' => [
                'en' => $this->faker->sentence,
                'hu' => $this->faker->sentence
            ]
        ];

        $responseData = $this->assertSuccessfulHttpApiRequest($this->_url, 'POST', $token, $data, true);

        $this->assertNotEmpty($responseData['data']['id']);
        $this->assertArraySubset($data, (array)$responseData['data']);
        $this->assertEquals((new UserEntity($user))->getFrontendData(), $responseData['data']['user']);

    }


    /**
     * @test
     * @group controller-write
     */
    public function it_can_delete_a_review()
    {
        list($token,) = $this->login([Config::get('stylersauth.role_admin')]);

        $responseData = $this->assertSuccessfulHttpApiRequest(
            $this->_url . '/' . $this->reviews->first()->id, 'DELETE', $token, [], true
        );

        $this->assertEmpty($responseData['data']);
    }

}
