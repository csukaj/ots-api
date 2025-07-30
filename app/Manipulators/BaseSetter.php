<?php

namespace App\Manipulators;


use App\Exceptions\UserException;
use Illuminate\Support\Facades\Validator;

class BaseSetter
{
    /**
     * Attributes that can be set from input
     */
    protected $attributes = ['id' => null];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = ['id' => 'integer|nullable'];

    /**
     * BaseSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes = [])
    {
        $this->loadAttributes($attributes);

        $validator = Validator::make($attributes, $this->rules);
        if ($validator->fails()) {
            throw new UserException($validator->errors()->first());
        }

    }

    protected function loadAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }
    }

}