<?php

namespace App\Manipulators;

use App\Manipulators\Abstracts\ModelPropertySetter;
use App\OrganizationClassification;
use App\OrganizationMeta;

/**
 * Manipulator to create a new OrganizationClassification or OrganizationMeta
 * instance after the supplied data passes validation
 */
class OrganizationPropertySetter extends ModelPropertySetter {

    protected $classificationClass = OrganizationClassification::class;
    protected $metaClass = OrganizationMeta::class;
    protected $foreignKey = 'organization_id';
    protected $categoryTxPath = 'taxonomies.organization_properties.category';
    protected $metaTxPath = 'taxonomies.organization_meta';

}
