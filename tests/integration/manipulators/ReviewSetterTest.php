<?php

namespace Tests\Integration\Manipulators;

use App\Entities\UserEntity;
use App\Exceptions\UserException;
use App\Manipulators\ReviewSetter;
use App\Organization;
use App\Review;
use App\User;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Tests\TestCase;

class ReviewSetterTest extends TestCase
{

    private $organization;
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->organization = factory(Organization::class, 'accommodation')->create();
        $this->user = factory(User::class)->create();
    }

    /**
     * @test
     * @throws UserException
     * @throws \Throwable
     */
    function it_can_be_set()
    {
        $data = [
            'review_subject_type' => Organization::class,
            'review_subject_id' => $this->organization->id,
            'user' => (new UserEntity($this->user))->getFrontendData(),
            'description' => [
                'en' => $this->faker->sentence,
                'hu' => $this->faker->sentence
            ]
        ];

        $actual = (new ReviewSetter($data))->set();

        $this->assertInstanceOf(Review::class, $actual);
        $this->assertNotEmpty($actual->id);
        $this->assertEquals($data['review_subject_type'], $actual->review_subject_type);
        $this->assertEquals($data['review_subject_id'], $actual->review_subject_id);
        $this->assertEquals($this->user->id, $actual->author_user_id);
        $this->assertEquals($data['description'], (new DescriptionEntity($actual->description))->getFrontendData());
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_cannot_be_set_with_bad_input_data()
    {
        $data = [
            'review_subject_type' => Organization::class,
            'user' => (new UserEntity($this->user))->getFrontendData(),
            'description' => [
                'en' => $this->faker->sentence,
                'hu' => $this->faker->sentence
            ]
        ];

        $this->expectException(UserException::class);
        (new ReviewSetter($data))->set();

        $data = [
            'review_subject_type' => Organization::class,
            'review_subject_id' => $this->organization->id,
            'user' => 1,
            'description' => [
                'en' => $this->faker->sentence,
                'hu' => $this->faker->sentence
            ]
        ];

        $this->expectException(UserException::class);
        (new ReviewSetter($data))->set();

        $data = [
            'review_subject_type' => Organization::class,
            'review_subject_id' => $this->organization->id,
            'user' => (new UserEntity($this->user))->getFrontendData(),
            'description' => [
                'hu' => $this->faker->sentence
            ]
        ];

        $this->expectException(UserException::class);
        (new ReviewSetter($data))->set();

        $data = [
            'review_subject_type' => Organization::class,
            'review_subject_id' => $this->faker->numberBetween(1000000,10000000),
            'user' => (new UserEntity($this->user))->getFrontendData(),
            'description' => [
                'en' => $this->faker->sentence,
                'hu' => $this->faker->sentence
            ]
        ];

        $this->expectException(UserException::class);
        (new ReviewSetter($data))->set();
    }

    /**
     * @test
     * @throws \Throwable
     */
    function it_can_updated()
    {

        $data = [
            'review_subject_type' => Organization::class,
            'review_subject_id' => $this->organization->id,
            'user' => (new UserEntity($this->user))->getFrontendData(),
            'description' => [
                'en' => $this->faker->sentence,
                'hu' => $this->faker->sentence
            ]
        ];

        $review = (new ReviewSetter($data))->set();

        $data['id'] = $review->id;
        $data['description']['en'] = $this->faker->sentence;
        $reviewUpdated = (new ReviewSetter($data))->set();
        $this->assertEquals($review->id, $reviewUpdated->id);
        $this->assertEquals($data['description']['en'], $reviewUpdated->description->description);
    }


}