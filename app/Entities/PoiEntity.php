<?php
namespace App\Entities;

use App\Poi;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class PoiEntity extends Entity
{

    protected $model;

    public function __construct(Poi $poi)
    {
        parent::__construct($poi);
    }

    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'type' => $this->model->type->name,
            'name' => (new DescriptionEntity($this->model->name))->getFrontendData(),
            'description' => $this->model->description_description_id ? (new DescriptionEntity($this->model->description))->getFrontendData() : [],
            'location' => (new LocationEntity($this->model->location))->getFrontendData(['admin'])
        ];
    }
}
