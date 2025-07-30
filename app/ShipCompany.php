<?php
namespace App;

use App\Facades\Config;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * App\ShipCompany
 *
 * @property int $id
 * @property int $name_description_id
 * @property int $type_taxonomy_id
 * @property bool $is_active
 * @property int|null $parentable_id
 * @property int|null $pricing_logic_taxonomy_id
 * @property int|null $margin_type_taxonomy_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property int|null $location_id
 * @property string|null $parentable_type
 * @property int|null $supplier_id
 * @property int|null $short_name_description_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\AgeRange[] $ageRanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Organization[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationClassification[] $classifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DateRange[] $dateRanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationDescription[] $descriptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Device[] $devices
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylersmedia\Entities\Gallery[] $galleries
 * @property-read \App\Location $location
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationManager[] $managers
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $marginType
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationMeta[] $metas
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ModelMealPlan[] $modelMealPlans
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $name
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $parentable
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $pricingLogic
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShipGroup[] $shipGroups
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Ship[] $ships
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $shortName
 * @property-read \App\Supplier|null $supplier
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization forParentable($type, $id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereParentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereParentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany wherePricingLogicTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereShortNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShipCompany whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\ShipCompany|null $organizationGroup
 */
class ShipCompany extends Organization
{
    use CascadeSoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organizations';

    protected $cascadeDeletes = ['shipGroups'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('shipCompany', function (Builder $builder) {
            $builder->where('type_taxonomy_id', Config::getOrFail('taxonomies.organization_types.ship_company.id'));
        });
    }

    public function getMorphClass()
    {
        return Organization::class;
    }

    /**
     * Relation to ships
     *
     * @return HasManyThrough
     */
    public function ships(): HasManyThrough
    {
        return $this->hasManyThrough(Ship::class, ShipGroup::class, 'parentable_id', 'parentable_id');
    }

    public function shipGroups()
    {
        return $this->hasMany(ShipGroup::class, 'parent_id', 'id');
    }
}
