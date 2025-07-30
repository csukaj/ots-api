<?php

namespace App;

use App\Facades\Config;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\ShipGroup
 *
 * @property int $id
 * @property int $name_description_id
 * @property int $type_taxonomy_id
 * @property bool $is_active
 * @property int $parent_id
 * @property int $pricing_logic_taxonomy_id
 * @property int $margin_type_taxonomy_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property int $location_id
 * @property-read Collection|AgeRange[] $ageRanges
 * @property-read Collection|OrganizationGroupClassification[] $classifications
 * @property-read Collection|DateRange[] $dateRanges
 * @property-read Collection|OrganizationGroupDescription[] $descriptions
 * @property-read Collection|Device[] $devices
 * @property-read Collection|Gallery[] $galleries
 * @property-read Location $location
 * @property-read Collection|OrganizationManager[] $managers
 * @property-read Taxonomy $marginType
 * @property-read Collection|OrganizationGroupMeta[] $metas
 * @property-read Description $name
 * @property-read Collection|ModelMealPlan[] $modelMealPlans
 * @property-read Organization $parentOrganization
 * @property-read Collection|Price[] $prices
 * @property-read Taxonomy $pricingLogic
 * @property-read Taxonomy $type
 * @mixin \Eloquent
 * @property float|null $margin_value
 * @property int|null $supplier_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Organization[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Cruise[] $cruises
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationGroupPoi[] $organizationGroupPois
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Product[] $products
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ship[] $ships
 * @property-read \App\Supplier|null $supplier
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup wherePricingLogicTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipGroup whereUpdatedAt($value)
 */
class ShipGroup extends OrganizationGroup
{
    use CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organization_groups';

    protected $cascadeDeletes = ['ships','products'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('ship_group', function (Builder $builder) {
            $builder->where('type_taxonomy_id', Config::getOrFail('taxonomies.organization_group_types.ship_group.id'));
        });
    }

    /**
     * Relation to ships
     *
     * @return MorphMany
     */
    public function ships(): MorphMany
    {
        return $this->morphMany(Ship::class, 'parentable');
    }

    /**
     * Relation to Products
     *
     * @return MorphMany
     */
    public function products(): MorphMany
    {
        return $this->morphMany(Product::class, 'productable');
    }

    /**
     * Relation to Cruises
     *
     * @return HasMany
     */
    public function cruises(): HasMany
    {
        return $this->hasMany(Cruise::class, 'ship_group_id', 'id');
    }

    /**
     * Relation to POIs
     *
     * @return HasMany
     */
    public function organizationGroupPois(): HasMany
    {
        return $this->hasMany(OrganizationGroupPoi::class, 'organization_group_id', 'id');
    }

    public function getHomePortLocations(): array
    {
        $locations = [];
        foreach ($this->organizationGroupPois as $orgGrpPoi) {
            $locations[] = $orgGrpPoi->poi->location;
        }
        return $locations;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getShipCount(): int
    {
        return Organization::
        forParentable(ShipGroup::class, $this->id)
            ->where("type_taxonomy_id", Config::getOrFail('taxonomies.organization_types.ship.id'))
            ->count();
    }

    public function getCabinCount(): int
    {
        return Device::forDeviceable(OrganizationGroup::class, $this->id)->sum('amount');
    }
}
