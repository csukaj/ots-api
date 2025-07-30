<?php

namespace App\Http\Controllers\Admin;

use App\Entities\OrganizationGroupClassificationEntity;
use App\Http\Controllers\Abstracts\ModelClassificationController;
use App\Manipulators\OrganizationGroupPropertySetter;
use App\OrganizationGroupClassification;

/**
 * @resource Admin/OrganizationGroupClassificationController
 */
class OrganizationGroupClassificationController extends ModelClassificationController {

    protected $classificationClass = OrganizationGroupClassification::class;
    protected $classificationEntityClass = OrganizationGroupClassificationEntity::class;
    protected $propertySetterClass = OrganizationGroupPropertySetter::class;
    protected $foreignKey = 'organization_group_id';

}
