<?php

namespace App\Entities;

use App\DeviceMeta;

class DeviceMetaEntity extends ModelMetaEntity
{
    public function __construct(DeviceMeta $devMt)
    {
        parent::__construct($devMt);
    }
}