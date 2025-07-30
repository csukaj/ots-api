<?php

namespace App\Entities;

use App\ProgramClassification;

class ProgramClassificationEntity extends ModelClassificationEntity
{
    protected $foreignKey = 'program_id';
    protected $metaEntity = ProgramMetaEntity::class;
    static protected $classificationTaxonomyKey = 'taxonomies.program_classification';

    public function __construct(ProgramClassification $progClass)
    {
        parent::__construct($progClass);
    }

}