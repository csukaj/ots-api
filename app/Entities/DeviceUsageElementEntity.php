<?php

namespace App\Entities;

use App\DeviceUsageElement;

class DeviceUsageElementEntity extends Entity
{
    protected $model;
    protected $elements;
    protected $ageRange;

    public function __construct(DeviceUsageElement $usageElement)
    {
        parent::__construct($usageElement);
    }

    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'amount' => $this->model->amount,
            'age_range' => $this->model->ageRange ? (new AgeRangeEntity($this->model->ageRange))->getFrontendData() : null,
        ];
    }
}