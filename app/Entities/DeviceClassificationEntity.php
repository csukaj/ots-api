<?php

namespace App\Entities;

use App\DeviceClassification;

class DeviceClassificationEntity extends ModelClassificationEntity
{
    protected $foreignKey = 'device_id';
    protected $metaEntity = DeviceMetaEntity::class;
    static protected $classificationTaxonomyKey = 'taxonomies.device_classification';

    public function __construct(DeviceClassification $devCl)
    {
        parent::__construct($devCl);
    }

}
