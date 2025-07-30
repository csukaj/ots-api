<?php

namespace App;

use App\Entities\CruiseMetaEntity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\CruiseMeta
 *
 * @property int $id
 * @property int $organization_group_id
 * @property int $parent_classification_id
 * @property int $taxonomy_id
 * @property string $value
 * @property int $priority
 * @property int $additional_description_id
 * @property bool $is_listable
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $additionalDescription
 * @property-read Taxonomy $metaTaxonomy
 * @property-read Cruise $cruise
 * @property-read CruiseClassification $parentClassification
 * @mixin \Eloquent
 * @property int $cruise_id
 * @property-read \App\Cruise $Cruise
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereCruiseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseMeta whereValue($value)
 */
class CruiseMeta extends ModelMeta
{

    protected $classificationClass = CruiseClassification::class;
    static protected $entityClass = CruiseMetaEntity::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cruise_id',
        'parent_classification_id',
        'taxonomy_id',
        'value',
        'priority',
        'additional_description_id',
        'is_listable'
    ];

    /**
     * Relation to cruise
     *
     * @return HasOne
     */
    public function Cruise(): HasOne
    {
        return $this->hasOne(Cruise::class);
    }
}
