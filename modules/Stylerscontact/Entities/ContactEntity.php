<?php

namespace Modules\Stylerscontact\Entities;


use App\Entities\Entity;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ContactEntity extends Entity
{
    protected $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->contact->id,
            'type' => (new TaxonomyEntity($this->contact->type))->getFrontendData(['translations']),
            'value' => $this->contact->value,
            'is_public' => $this->contact->is_public
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'contactable':
                    $return['contactable_type'] =  $this->contact->contactable_type;
                    $return['contactable_id'] =  $this->contact->contactable_id;
                    break;
            }
        }

        return $return;
    }
}
