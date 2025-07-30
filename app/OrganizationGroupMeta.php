<?php

namespace App;

use App\Entities\OrganizationGroupMetaEntity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OrganizationGroupMeta
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
 * @property-read OrganizationGroup $organization
 * @property-read OrganizationGroupClassification $parentClassification
 * @mixin \Eloquent
 * @property-read \App\OrganizationGroup $organizationGroup
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta forParent($parent_classification_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta listable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMeta searchable()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereAdditionalDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereIsListable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereOrganizationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereParentClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupMeta whereValue($value)
 */
class OrganizationGroupMeta extends ModelMeta
{

    protected $classificationClass = OrganizationGroupClassification::class;
    static protected $entityClass = OrganizationGroupMetaEntity::class;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_group_id',
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
    public function organizationGroup(): HasOne
    {
        return $this->hasOne(OrganizationGroup::class);
    }
}
