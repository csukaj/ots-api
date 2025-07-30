<?php

namespace App\Entities;

use App\OrganizationMeta;

class OrganizationMetaEntity extends ModelMetaEntity
{
    public function __construct(OrganizationMeta $orgMt)
    {
        parent::__construct($orgMt);
    }
}
