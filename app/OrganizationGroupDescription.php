<?php

namespace App;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OrganizationGroupDescription
 *
 * @property int $id
 * @property int $organization_group_id
 * @property int $taxonomy_id
 * @property int $description_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $description
 * @property-read Taxonomy $descriptionTaxonomy
 * @property-read OrganizationGroup $organization
 * @mixin \Eloquent
 * @property-read \App\OrganizationGroup $organizationGroup
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroupDescription onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupDescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupDescription whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupDescription whereDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupDescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupDescription whereOrganizationGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupDescription whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroupDescription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroupDescription withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroupDescription withoutTrashed()
 */
class OrganizationGroupDescription extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        DescriptionTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['organization_group_id', 'taxonomy_id', 'description_id'];

    protected $cascadeDeletes = ['description'];

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
