<?php

namespace Modules\Stylerscontact\Entities;


use App\Entities\Entity;

class PersonEntity extends Entity
{
    protected $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->person->id,
            'name' => $this->person->name,
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'personable':
                    $return['personable_type'] = $this->person->personable_type;
                    $return['personable_id'] = $this->person->personable_id;
                    break;
                case 'contacts':
                    $return['contacts'] = ContactEntity::getCollection($this->person->contacts);
                    break;
            }
        }

        return $return;
    }
}
