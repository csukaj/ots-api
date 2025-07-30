<?php

namespace App\Entities;

use App\DeviceUsage;

class DeviceUsageEntity extends Entity
{
    protected $model;
    protected $elements;
    protected $ageRange;
    protected $fromDate;
    protected $toDate;

    public function __construct(DeviceUsage $usage, $fromDate = null, $toDate = null)
    {
        parent::__construct($usage);
        $this->elements = $this->model->elements;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id,
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'public_elements':
                    $return['elements'] = [];
                    foreach ($this->elements as $element) {
                        $name = $element->ageRange->name->name;
                        $return['elements'][$name] = $element->amount;
                    }
                    break;
                case 'admin':
                    $return['device_id'] = $this->model->device_id;
                    $return['elements'] = DeviceUsageElementEntity::getCollection($this->model->elements()->with('ageRange')->get());
                    break;
            }
        }

        return $return;
    }

}