<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Review;
use App\User;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new Review
 * instance after the supplied data passes validation
 */
class ReviewSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'review_subject_type' => null,
        'review_subject_id' => null,
        'author_user_id' => null,
        'review_description_id' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'review_subject_type' => 'required|string',
        'review_subject_id' => 'required|int',
        'user' => 'required|array',
        'user.id' => 'required|int',
        'description' => 'required|array',
        'description.en' => 'required'
    ];

    private $description;

    /**
     * Constructs Setter and validates input data
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->attributes['author_user_id'] = User::findOrFail($attributes['user']['id'])->id;
        $this->description = $attributes['description'];
        $this->attributes['review_subject_type']::findOrFail($this->attributes['review_subject_id']); // check if morph object exitst
    }

    /**
     * @return Review
     * @throws \Throwable
     */
    public function set(): Review
    {
        $descriptionId = null;
        if ($this->attributes['id']) {
            $descriptionId = Review::withTrashed()->findOrFail($this->attributes['id'])->review_description_id;
        }
        $this->attributes['review_description_id'] = (new DescriptionSetter($this->description, $descriptionId))
            ->set()
            ->id;
        $attributes = [
            'review_subject_type' => $this->attributes['review_subject_type'],
            'review_subject_id' => $this->attributes['review_subject_id'],
            'author_user_id' => $this->attributes['author_user_id'],
            'review_description_id' => $this->attributes['review_description_id']
        ];
        $review = Review::createOrRestore($attributes, $this->attributes['id']);
        $review->fill($this->attributes)->saveOrFail();
        return $review;
    }


}
