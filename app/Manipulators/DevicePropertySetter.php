<?php

namespace App\Manipulators;

use App\DeviceClassification;
use App\DeviceMeta;
use App\Manipulators\Abstracts\ModelPropertySetter;

/**
 * Manipulator to create a new DeviceClassification
 * instance after the supplied data passes validation
 */
class DevicePropertySetter extends ModelPropertySetter
{

    protected $classificationClass = DeviceClassification::class;
    protected $metaClass = DeviceMeta::class;
    protected $foreignKey = 'device_id';
    protected $categoryTxPath = 'taxonomies.device_properties.category';
    protected $metaTxPath = 'taxonomies.device_meta';

}
