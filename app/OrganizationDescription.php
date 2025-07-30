<?php

namespace App;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OrganizationDescription
 *
 * @property int $id
 * @property int $organization_id
 * @property int $taxonomy_id
 * @property int $description_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $description
 * @property-read Taxonomy $descriptionTaxonomy
 * @property-read Organization $organization
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationDescription onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationDescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationDescription whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationDescription whereDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationDescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationDescription whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationDescription whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationDescription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationDescription withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationDescription withoutTrashed()
 */
class OrganizationDescription extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        DescriptionTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['organization_id', 'taxonomy_id', 'description_id'];

    protected $cascadeDeletes = ['description'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
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
