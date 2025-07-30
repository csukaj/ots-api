<?php

namespace App\Http\Controllers\Admin;

use App\Entities\ProgramClassificationEntity;
use App\Http\Controllers\Abstracts\ModelClassificationController;
use App\Manipulators\ProgramPropertySetter;
use App\ProgramClassification;

/**
 * @resource Admin/ProgramClassificationController
 */
class ProgramClassificationController extends ModelClassificationController {

    protected $classificationClass = ProgramClassification::class;
    protected $classificationEntityClass = ProgramClassificationEntity::class;
    protected $propertySetterClass = ProgramPropertySetter::class;
    protected $foreignKey = 'program_id';

}
