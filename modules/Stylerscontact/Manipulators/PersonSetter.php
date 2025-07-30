<?php

namespace Modules\Stylerscontact\Manipulators;

use App\Exceptions\UserException;
use Illuminate\Support\Facades\Validator;
use Modules\Stylerscontact\Entities\Person;

class PersonSetter
{
    private $attributes = [
        'id' => null,
        'personable_type' => null,
        'personable_id' => null,
        'name' => null,
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'personable_type' => 'required|string',
        'personable_id' => 'required|integer',
        'name' => 'required|string',
    ];

    /**
     * PersonSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }

        $validator = Validator::make($attributes, $this->rules);
        if ($validator->fails()) {
            throw new UserException($validator->errors()->first());
        }
    }


    /**
     * Creates new person
     * @return Person
     */
    public function set(): Person
    {
        $attributes = [
            'personable_type' => $this->attributes['personable_type'],
            'personable_id' => $this->attributes['personable_id'],
            'name' => $this->attributes['name'],
        ];
        $person = Person::createOrRestore($attributes, $this->attributes['id']);
        $person->fill($this->attributes)->saveOrFail();
        return $person;

    }

}