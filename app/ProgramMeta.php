<?php

namespace App;

use App\Entities\ProgramMetaEntity;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\ProgramMeta
 *
 * @property int $id
 * @property int $program_id
 * @property int|null $parent_classification_id
 * @property int $taxonomy_id
 * @property string $value
 * @property int|null $priority
 * @property int|null $additional_description_id
 * @property bool $is_listable
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $additionalDescription
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $metaTaxonomy
 * @property-read \App\ProgramClassification $parentClassification
 * @property-read \App\Program $program
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramMeta whereValue($value)
 * @mixin \Eloquent
 */
class ProgramMeta extends ModelMeta
{
    protected $classificationClass = ProgramClassification::class;
    static protected $entityClass = ProgramMetaEntity::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'program_id',
        'parent_classification_id',
        'taxonomy_id',
        'value',
        'priority',
        'additional_description_id',
        'is_listable'
    ];

    /**
     * Relation to organization
     *
     * @return HasOne
     */
    public function program(): HasOne {
        return $this->hasOne(Program::class);
    }
}
