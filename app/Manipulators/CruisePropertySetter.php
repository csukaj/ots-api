<?php

namespace App\Manipulators;

use App\Manipulators\Abstracts\ModelPropertySetter;
use App\CruiseClassification;
use App\CruiseMeta;

/**
 * Manipulator to create a new CruiseClassification or CruiseMeta
 * instance after the supplied data passes validation
 */
class CruisePropertySetter extends ModelPropertySetter
{

    protected $classificationClass = CruiseClassification::class;
    protected $metaClass = CruiseMeta::class;
    protected $foreignKey = 'cruise_id';
    protected $categoryTxPath = 'taxonomies.cruise_properties.category';
    protected $metaTxPath = 'taxonomies.cruise_meta';

}
