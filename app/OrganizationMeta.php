<?php

namespace App;

use App\Entities\OrganizationMetaEntity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OrganizationMeta
 *
 * @property int $id
 * @property int $organization_id
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
 * @property-read Organization $organization
 * @property-read OrganizationClassification $parentClassification
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationMeta whereValue($value)
 */
class OrganizationMeta extends ModelMeta
{

    protected $classificationClass = OrganizationClassification::class;
    static protected $entityClass = OrganizationMetaEntity::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'parent_classification_id',
        'taxonomy_id',
        'value',
        'priority',
        'additional_description_id',
        'is_listable'
    ];

    protected $touches = ['organization'];

    /**
     * Relation to organization
     *
     * @return HasOne
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
