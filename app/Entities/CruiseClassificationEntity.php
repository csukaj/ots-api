<?php

namespace App\Entities;

use App\CruiseClassification;

class CruiseClassificationEntity extends ModelClassificationEntity
{
    protected $foreignKey = 'cruise_id';
    protected $metaEntity = CruiseMetaEntity::class;
    static protected $classificationTaxonomyKey = 'taxonomies.cruise_classification';

    public function __construct(CruiseClassification $cruiseCl)
    {
        parent::__construct($cruiseCl);
    }

}