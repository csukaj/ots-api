<?php

namespace App\Http\Controllers\Admin;

use App\DeviceClassification;
use App\Entities\DeviceClassificationEntity;
use App\Http\Controllers\Abstracts\ModelClassificationController;
use App\Manipulators\DevicePropertySetter;

/**
 * @resource Admin/DeviceClassificationController
 */
class DeviceClassificationController extends ModelClassificationController
{

    protected $classificationClass = DeviceClassification::class;
    protected $classificationEntityClass = DeviceClassificationEntity::class;
    protected $propertySetterClass = DevicePropertySetter::class;
    protected $foreignKey = 'device_id';
}
