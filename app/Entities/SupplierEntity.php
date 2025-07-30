<?php

namespace App\Entities;

use App\Supplier;
use Modules\Stylerscontact\Entities\ContactEntity;
use Modules\Stylerscontact\Entities\PersonEntity;

class SupplierEntity extends OrganizationEntity
{

    public function __construct(Supplier $supplier)
    {
        parent::__construct($supplier);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->organization->id,
            'type' => $this->organization->type->name,
            'name' => $this->getDescriptionWithTranslationsData($this->organization->name),
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'accommodations':
                    $return['accommodations'] = AccommodationEntity::getCollection($this->organization->accommodations()->with('hotelChain')->get());
                    break;
                case 'contacts':
                    $return['contacts'] = ContactEntity::getCollection($this->organization->contacts);
                    break;
                case 'people':
                    $return['people'] = PersonEntity::getCollection($this->organization->people, ['contacts']);
                    break;
            }
        }

        return $return;
    }
}
