<?php

namespace App\Entities;

use App\OrganizationGroupPoi;

class OrganizationGroupPoiEntity extends Entity
{

    protected $model;

    public function __construct(OrganizationGroupPoi $poi)
    {
        parent::__construct($poi);
    }

    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'organization_group_id' => $this->model->organization_group_id,
            'type' => $this->model->type->name,
            'poi' => (new PoiEntity($this->model->poi))->getFrontendData()
        ];
    }
}
