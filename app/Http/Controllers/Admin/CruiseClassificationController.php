<?php

namespace App\Http\Controllers\Admin;

use App\Entities\CruiseClassificationEntity;
use App\Http\Controllers\Abstracts\ModelClassificationController;
use App\Manipulators\CruisePropertySetter;
use App\CruiseClassification;

/**
 * @resource Admin/CruiseClassificationController
 */
class CruiseClassificationController extends ModelClassificationController {

    protected $classificationClass = CruiseClassification::class;
    protected $classificationEntityClass = CruiseClassificationEntity::class;
    protected $propertySetterClass = CruisePropertySetter::class;
    protected $foreignKey = 'cruise_id';

}
