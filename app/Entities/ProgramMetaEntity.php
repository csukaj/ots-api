<?php

namespace App\Entities;

use App\ProgramMeta;

class ProgramMetaEntity extends ModelMetaEntity
{
    protected $programMeta;

    public function __construct(ProgramMeta $programMeta)
    {
        parent::__construct($programMeta);
    }

}