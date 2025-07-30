<?php

namespace App\Http\Controllers\Admin;

use App\Entities\OrganizationClassificationEntity;
use App\Http\Controllers\Abstracts\ModelClassificationController;
use App\Manipulators\OrganizationPropertySetter;
use App\OrganizationClassification;

/**
 * @resource Admin/OrganizationClassificationController
 */
class OrganizationClassificationController extends ModelClassificationController {

    protected $classificationClass = OrganizationClassification::class;
    protected $classificationEntityClass = OrganizationClassificationEntity::class;
    protected $propertySetterClass = OrganizationPropertySetter::class;
    protected $foreignKey = 'organization_id';

}
