<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylerstaxonomy\Entities\ClassificableTrait;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Program
 *
 * @property int $id
 * @property int $type_taxonomy_id
 * @property int $name_description_id
 * @property int $location_id
 * @property int $organization_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProgramRelation[] $childRelations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProgramDescription[] $descriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylersmedia\Entities\Gallery[] $galleries
 * @property-read \App\Location $location
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProgramRelation[] $parentRelations
 * @property-read \App\Product $product
 * @property-read \App\ShipCompany $shipCompany
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $type
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Program onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Program whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Program whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Program whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Program whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Program whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Program whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Program whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Program whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Program withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Program withoutTrashed()
 * @mixin \Eloquent
 */
class Program extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        ClassificableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_taxonomy_id',
        'name_description_id',
        'location_id',
        'organization_id'
    ];

    protected $cascadeDeletes = ['name','descriptions','product','galleries'];

    /**
     * Relation to ship company
     *
     * @return BelongsTo
     */
    public function shipCompany(): BelongsTo
    {
        return $this->belongsTo(ShipCompany::class, 'organization_id');
    }

    /**
     * Relation to type
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
     * Relation to Program descriptions
     *
     * @return HasMany
     */
    public function descriptions()
    {
        return $this->hasMany(ProgramDescription::class, 'program_id', 'id');
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

    /**
     * Relation to parent relations
     *
     * @return HasMany
     */
    public function parentRelations(): HasMany
    {
        return $this->hasMany(ProgramRelation::class, 'child_id', 'id');
    }

    /**
     * Relation to child relations
     *
     * @return HasMany
     */
    public function childRelations(): HasMany
    {
        return $this->hasMany(ProgramRelation::class, 'parent_id', 'id');
    }

    /**
     * Relation to child relations
     *
     * @return MorphOne
     */
    public function product(): MorphOne
    {
        return $this->morphOne(Product::class, 'productable');
    }

    /**
     * Relation to galleries
     *
     * @return MorphMany
     */
    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

}
