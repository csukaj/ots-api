<?php

namespace App;

use App\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OrganizationGroupPoi
 *
 * @property int $id
 * @property int $type_taxonomy_id
 * @property int $organization_group_id
 * @property int $poi_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Taxonomy $type
 * @property-read Description $name
 * @property-read Description $description
 * @property-read Location $location
 * @mixin \Eloquent
 * @property-read \App\OrganizationGroup $organizationGroup
 * @property-read \App\Poi $poi
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroupPoi onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupPoi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupPoi whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupPoi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupPoi whereOrganizationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupPoi wherePoiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupPoi whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupPoi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroupPoi withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroupPoi withoutTrashed()
 */
class OrganizationGroupPoi extends Model
{

    use SoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_taxonomy_id',
        'organization_group_id',
        'poi_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Relation to type taxonomy
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    /**
     * Relation to name description
     *
     * @return BelongsTo
     */
    public function organizationGroup(): BelongsTo
    {
        return $this->belongsTo(OrganizationGroup::class, 'id', 'organization_group_id');
    }

    /**
     * Relation to location
     *
     * @return HasOne
     */
    public function poi(): HasOne
    {
        return $this->hasOne(Poi::class, 'id', 'poi_id');
    }

}
