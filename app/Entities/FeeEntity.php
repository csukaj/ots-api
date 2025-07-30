<?php

namespace App\Entities;

use App\Fee;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class FeeEntity extends Entity
{

    protected $model;

    public function __construct(Fee $fee)
    {
        parent::__construct($fee);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'name' => (new DescriptionEntity($this->model->product->name))->getFrontendData(),
            'type' => $this->model->product->type->name,
            'age_range' => $this->model->ageRange ? $this->model->ageRange->name->name : null,
            'rack_price' => $this->model->rack_price
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'admin':
                    $return['id'] = $this->model->id;
                    $return['product_id'] = $this->model->product_id;
                    $return['net_price'] = $this->model->net_price;
                    $return['margin_type'] = $this->model->marginType ? $this->model->marginType->name : null;
                    $return['margin_value'] = $this->model->margin_value;
                    break;
            }
        }

        return $return;
    }
}
