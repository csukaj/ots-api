<?php

namespace App\Entities;

use App\Review;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class ReviewEntity extends Entity
{

    protected $model;

    public function __construct(Review $review)
    {
        parent::__construct($review);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'user' => $this->model->author->name,
            'description' => (new DescriptionEntity($this->model->description))->getFrontendData()
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'admin':
                    $return['id'] = $this->model->id;
                    $return['review_subject_type'] = $this->model->review_subject_type;
                    $return['review_subject_id'] = $this->model->review_subject_id;
                    $return['user'] = (new UserEntity($this->model->author))->getFrontendData();
                    break;
            }
        }

        return $return;
    }
}
