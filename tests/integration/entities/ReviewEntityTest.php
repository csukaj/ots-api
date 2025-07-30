<?php

namespace Tests\Integration\Entities;

use App\Entities\ReviewEntity;
use App\Entities\UserEntity;
use App\Review;
use Tests\TestCase;

class ReviewEntityTest extends TestCase
{

    static public $setupMode = self::SETUPMODE_ONCE;
    private $model;

    public function setUp()
    {
        parent::setUp();
        $this->model = factory(Review::class)->create();
    }

    /**
     * @test
     */
    function a_review_has_public_data()
    {
        $review = $this->model;
        $reviewData = (new ReviewEntity($review))->getFrontendData();

        $expected = ['user' => $review->author->name, 'description' => ['en' => $review->description->description]];
        $this->assertEquals($expected, $reviewData);
    }

    /**
     * @test
     */
    function a_review_has_admin_data()
    {
        $review = $this->model;
        $reviewData = (new ReviewEntity($review))->getFrontendData(['admin']);

        $expected = [
            'id' => $review->id,
            'description' => ['en' => $review->description->description],
            'review_subject_type' => $review->review_subject_type,
            'review_subject_id' => $review->review_subject_id,
            'user' => (new UserEntity($review->author))->getFrontendData()
        ];

        $this->assertEquals($expected, $reviewData);
    }
}
