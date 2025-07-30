<?php

namespace App\Entities;

use App\OrganizationGroupClassification;

class OrganizationGroupClassificationEntity extends ModelClassificationEntity
{
    protected $foreignKey = 'organization_id';
    protected $metaEntity = OrganizationGroupMetaEntity::class;
    static protected $classificationTaxonomyKey = 'taxonomies.organization_group_classification';

    public function __construct(OrganizationGroupClassification $orgCl)
    {
        parent::__construct($orgCl);
    }

}