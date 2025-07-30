<?php

namespace App\Entities;

use App\Location;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class LocationEntity extends Entity
{

    protected $model;

    public function __construct(Location $location)
    {
        parent::__construct($location);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'frontend':
                    $return += [
                        'island' => $this->model->island_id ? $this->model->island->name->name : null,
                        'district' => $this->model->district_id ? $this->model->district->name->name : null,
                        'longitude' => $this->model->longitude,
                        'latitude' => $this->model->latitude,
                        'po_box' => $this->model->po_box
                    ];
                    break;
                case 'admin':
                    $return += [
                        'island' => [
                            'id' => $this->model->island_id,
                            'name_taxonomy' => $this->model->island_id ? (new TaxonomyEntity($this->model->island->name))->getFrontendData() : null
                        ],
                        'district' => [
                            'id' => $this->model->district_id,
                            'name_taxonomy' => $this->model->district_id ? (new TaxonomyEntity($this->model->district->name))->getFrontendData() : null
                        ],
                        'longitude' => $this->model->longitude,
                        'latitude' => $this->model->latitude,
                        'po_box' => $this->model->po_box
                    ];
                    break;
            }
        }

        return $return;
    }

}
