<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\ProgramClassification
 *
 * @property int $id
 * @property int $program_id
 * @property int|null $parent_classification_id
 * @property int $classification_taxonomy_id
 * @property int|null $value_taxonomy_id
 * @property int|null $priority
 * @property int|null $charge_taxonomy_id
 * @property int|null $additional_description_id
 * @property bool $is_highlighted
 * @property bool $is_listable
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $additionalDescription
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy|null $chargeTaxonomy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProgramClassification[] $childClassifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProgramMeta[] $childMetas
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $classificationTaxonomy
 * @property-read \App\ProgramClassification|null $parentClassification
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $priceTaxonomy
 * @property-read \App\Program $program
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy|null $valueTaxonomy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelClassification searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereChargeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereClassificationTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereIsHighlighted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramClassification whereValueTaxonomyId($value)
 * @mixin \Eloquent
 */
class ProgramClassification extends ModelClassification
{
    protected $classificationClass = self::class;
    protected $metaClass = ProgramMeta::class;
    protected static $foreignKey = 'program_id';

    protected $fillable = [
        'program_id',
        'parent_classification_id',
        'classification_taxonomy_id',
        'value_taxonomy_id',
        'priority',
        'charge_taxonomy_id',
        'additional_description_id',
        'is_highlighted',
        'is_listable'
    ];

    /**
     * Relation to organization
     *
     * @return HasOne
     */
    public function program()
    {
        return $this->hasOne(Program::class, 'id', self::$foreignKey);
    }

}
