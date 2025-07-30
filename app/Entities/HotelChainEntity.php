<?php

namespace App\Entities;

use App\HotelChain;

class HotelChainEntity extends OrganizationEntity
{

    public function __construct(HotelChain $hotelChain)
    {
        parent::__construct($hotelChain);
    }

    public function getFrontendData(array $additions = [], string $productType = null): array
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
            }
        }

        return $return;
    }
}
