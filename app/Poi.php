<?php

namespace App;

use App\Traits\ModelTrait;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Poi
 *
 * @property int $id
 * @property int $type_taxonomy_id
 * @property int $name_description_id
 * @property int $description_description_id
 * @property int $location_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Taxonomy $type
 * @property-read Description $name
 * @property-read Description $description
 * @property-read Location $location
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Poi onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Poi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Poi whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Poi whereDescriptionDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Poi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Poi whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Poi whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Poi whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Poi whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Poi withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Poi withoutTrashed()
 */
class Poi extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_taxonomy_id',
        'name_description_id',
        'description_description_id',
        'location_id'
    ];

    protected $cascadeDeletes = ['name', 'description'];

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
     * @return HasOne
     */
    public function name(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'name_description_id');
    }

    /**
     * Relation to description description
     *
     * @return HasOne
     */
    public function description(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'description_description_id');
    }

    /**
     * Relation to location
     *
     * @return HasOne
     */
    public function location(): HasOne
    {
        return $this->hasOne(Location::class, 'id', 'location_id');
    }

}
