<?php

namespace Modules\Stylerscontact\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Modules\Stylerscontact\Entities\Contact;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

class ContactSetter
{
    private $attributes = [
        'id' => null,
        'contactable_type' => null,
        'contactable_id' => null,
        'type_taxonomy_id' => null,
        'value' => null,
        'priority' => null,
        'is_public' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'contactable_type' => 'required|string',
        'contactable_id' => 'required|integer',
        'type' => 'required',
        'value' => 'required',
        'is_public' => 'required|boolean'
    ];

    /**
     * ContactSetter constructor.
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

        if (isset($this->attributes['priority']) && $this->priorityExists($this->attributes['contactable_type'],
                $this->attributes['contactable_id'], $this->attributes['priority'])) {
            throw new UserException('Priority already exists.');
        }
        if (Taxonomy::taxonomyExists($attributes['type']['name'], Config::get('taxonomies.contact_type'))) {
            $this->attributes['type_taxonomy_id'] = Taxonomy::getTaxonomy($attributes['type']['name'],
                Config::get('taxonomies.contact_type'))->id;
        } else {
            throw new UserException('Bad Type');
        }

    }


    /**
     * Creates new contact
     * @return Contact
     */
    public function set(): Contact
    {
        $attributes = [
            'contactable_type' => $this->attributes['contactable_type'],
            'contactable_id' => $this->attributes['contactable_id'],
            'type_taxonomy_id' => $this->attributes['type_taxonomy_id'],
            'value' => $this->attributes['value']
        ];
        $contact = Contact::createOrRestore($attributes, $this->attributes['id']);
        $contact->fill($this->attributes)->saveOrFail();
        return $contact;

    }

    /**
     * @param string $contactableType
     * @param int $contactableId
     * @param int $priority
     * @return bool
     */
    private function priorityExists(string $contactableType, int $contactableId, int $priority): bool
    {
        return Contact
            ::forContactable($contactableType, $contactableId)
            ->where('priority', $priority)
            ->exists();
    }
}