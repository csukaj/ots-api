<?php

namespace App\Entities;

use App\OrganizationGroupMeta;

class OrganizationGroupMetaEntity extends ModelMetaEntity
{
    public function __construct(OrganizationGroupMeta $orgMt)
    {
        parent::__construct($orgMt);
    }
}
