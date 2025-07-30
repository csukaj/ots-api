<?php

namespace App\Manipulators;

use App\Manipulators\Abstracts\ModelPropertySetter;
use App\ProgramClassification;
use App\ProgramMeta;

/**
 * Manipulator to create a new ProgramClassification or ProgramMeta
 * instance after the supplied data passes validation
 */
class ProgramPropertySetter extends ModelPropertySetter
{

    protected $classificationClass = ProgramClassification::class;
    protected $metaClass = ProgramMeta::class;
    protected $foreignKey = 'program_id';
    protected $categoryTxPath = 'taxonomies.program_properties.category';
    protected $metaTxPath = 'taxonomies.program_meta';

}
