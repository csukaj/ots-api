<?php

namespace App\Entities;

use App\DeviceMinimumNights;

class DeviceMinimumNightsEntity extends Entity
{

    protected $model;

    public function __construct(DeviceMinimumNights $deviceMinimumNights)
    {
        parent::__construct($deviceMinimumNights);
    }

    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'device_id' => $this->model->device_id,
            'date_range_id' => $this->model->date_range_id,
            'minimum_nights' => $this->model->minimum_nights
        ];
    }

}
