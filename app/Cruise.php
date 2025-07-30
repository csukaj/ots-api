<?php

namespace App;

use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylerstaxonomy\Entities\ClassificableTrait;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Cruise
 *
 * @property int $id
 * @property int $name_description_id
 * @property bool $is_active
 * @property int $ship_company_id
 * @property int $ship_group_id
 * @property int $pricing_logic_taxonomy_id
 * @property int $margin_type_taxonomy_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property int $location_id
 * @property-read Collection|AgeRange[] $ageRanges
 * @property-read Collection|CruiseClassification[] $classifications
 * @property-read Collection|DateRange[] $dateRanges
 * @property-read Collection|CruiseDescription[] $descriptions
 * @property-read Collection|Device[] $devices
 * @property-read Collection|Gallery[] $galleries
 * @property-read Location $location
 * @property-read Collection|OrganizationManager[] $managers
 * @property-read Taxonomy $marginType
 * @property-read Collection|CruiseMeta[] $metas
 * @property-read Description $name
 * @property-read Organization $parentOrganization
 * @property-read Collection|Price[] $prices
 * @property-read Taxonomy $pricingLogic
 * @property-read Taxonomy $type
 * @mixin \Eloquent
 * @property int|null $itinerary_id
 * @property int|null $supplier_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CruiseDevice[] $cruiseDevices
 * @property-read \App\Program $itinerary
 * @property-read \App\ShipCompany|null $shipCompany
 * @property-read \App\ShipGroup $shipGroup
 * @property-read \App\Supplier|null $supplier
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Cruise onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereItineraryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise wherePricingLogicTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereShipCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereShipGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Cruise whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Cruise withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Cruise withoutTrashed()
 */
class Cruise extends Model
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
        'name_description_id',
        'is_active',
        'ship_company_id',
        'ship_group_id',
        'itinerary_id',
        'pricing_logic_taxonomy_id',
        'margin_type_taxonomy_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'location_id',
        'supplier_id'
    ];

    protected $cascadeDeletes = ['name','classifications','metas', 'descriptions', 'galleries', 'cruiseDevices', 'ageRanges', 'dateRanges'];

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
     * Relation to a ship company
     *
     * @return BelongsTo
     */
    public function shipCompany(): BelongsTo
    {
        return $this->belongsTo(ShipCompany::class, 'ship_company_id');
    }

    /**
     * Relation to a ship group
     *
     * @return HasOne
     */
    public function shipGroup(): HasOne
    {
        return $this->hasOne(ShipGroup::class, 'id', 'ship_group_id');
    }

    /**
     * Relation to an itinerary
     *
     * @return HasOne
     */
    public function itinerary(): HasOne
    {
        return $this->hasOne(Program::class, 'id', 'itinerary_id');
    }

    /**
     * Relation to cruise classifications
     *
     * @return HasMany
     */
    public function classifications(): HasMany
    {
        return $this->hasMany(CruiseClassification::class, 'cruise_id', 'id');
    }

    /**
     * Relation to Organization Metas
     *
     * @return HasMany
     */
    public function metas(): HasMany
    {
        return $this->hasMany(CruiseMeta::class, 'cruise_id', 'id');
    }

    /**
     * Relation to Organization Descriptions
     *
     * @return HasMany
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(CruiseDescription::class, 'cruise_id', 'id');
    }

    /**
     * Relation to pricing Logic
     *
     * @return HasOne
     */
    public function pricingLogic(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'pricing_logic_taxonomy_id');
    }

    /**
     * Relation to margin type taxonomy
     *
     * @return HasOne
     */
    public function marginType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'margin_type_taxonomy_id');
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
     * Relation to galleries
     *
     * @return MorphMany
     */
    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    /**
     * Relation to galleries
     *
     * @return MorphMany
     */
    public function ageRanges(): MorphMany
    {
        return $this->morphMany(AgeRange::class, 'age_rangeable');
    }
    
    /**
     * Relation to CruiseDevices
     *
     * @return HasMany
     */
    public function cruiseDevices(): HasMany
    {
        return $this->hasMany(CruiseDevice::class, 'cruise_id', 'id');
    }
    
    /**
     * Relation to date ranges
     *
     * @return MorphMany
     */
    public function dateRanges(): MorphMany
    {
        return $this->morphMany(DateRange::class, 'dateRangeable', 'date_rangeable_type', 'date_rangeable_id');
    }

    /**
     * Relation to supplier
     *
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }


    /**
     * Get active cruise ids
     *
     * @return array
     */
    static public function getIds(): array
    {
        return self::pluck('id')->toArray();
    }
}
