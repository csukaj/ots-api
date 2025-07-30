<?php

namespace App\Entities;

use App\CruiseMeta;

class CruiseMetaEntity extends ModelMetaEntity
{
    public function __construct(CruiseMeta $orgMt)
    {
        parent::__construct($orgMt);
    }
}
