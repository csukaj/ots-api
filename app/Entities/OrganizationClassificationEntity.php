<?php

namespace App\Entities;

use App\OrganizationClassification;

class OrganizationClassificationEntity extends ModelClassificationEntity
{
    protected $foreignKey = 'organization_id';
    protected $metaEntity = OrganizationMetaEntity::class;
    static protected $classificationTaxonomyKey = 'taxonomies.organization_classification';

    public function __construct(OrganizationClassification $orgCl)
    {
        parent::__construct($orgCl);
    }

}