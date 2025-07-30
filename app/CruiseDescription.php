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
 * App\CruiseDescription
 *
 * @property int $id
 * @property int $cruise_id
 * @property int $taxonomy_id
 * @property int $description_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $description
 * @property-read Taxonomy $descriptionTaxonomy
 * @property-read Cruise $cruise
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\CruiseDescription onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDescription whereCruiseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDescription whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDescription whereDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDescription whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDescription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\CruiseDescription withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\CruiseDescription withoutTrashed()
 */
class CruiseDescription extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        DescriptionTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cruise_id', 'taxonomy_id', 'description_id'];

    protected $cascadeDeletes = ['description'];

    /**
     * Relation to cruise
     *
     * @return HasOne
     */
    public function cruise(): HasOne
    {
        return $this->hasOne(Cruise::class);
    }

}
