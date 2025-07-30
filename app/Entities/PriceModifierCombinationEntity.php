<?php

namespace App\Entities;

use App\PriceModifierCombination;

class PriceModifierCombinationEntity extends Entity
{

    protected $model;

    public function __construct(PriceModifierCombination $priceModifierCombination)
    {
        parent::__construct($priceModifierCombination);
    }

    public function getFrontendData(array $additions = []): array
    {

        $return = [
            'id' => $this->model->id,
            'first_price_modifier_id' => $this->model->first_price_modifier_id,
            'second_price_modifier_id' => $this->model->second_price_modifier_id
        ];

        return $return;
    }

}
