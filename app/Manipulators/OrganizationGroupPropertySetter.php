<?php

namespace App\Manipulators;

use App\Manipulators\Abstracts\ModelPropertySetter;
use App\OrganizationGroupClassification;
use App\OrganizationGroupMeta;

/**
 * Manipulator to create a new OrganizationClassification or OrganizationMeta
 * instance after the supplied data passes validation
 */
class OrganizationGroupPropertySetter extends ModelPropertySetter {

    protected $classificationClass = OrganizationGroupClassification::class;
    protected $metaClass = OrganizationGroupMeta::class;
    protected $foreignKey = 'organization_group_id';
    protected $categoryTxPath = 'taxonomies.organization_group_properties.category';
    protected $metaTxPath = 'taxonomies.organization_group_meta';

}
