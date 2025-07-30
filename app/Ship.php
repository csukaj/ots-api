<?php
namespace App;

use App\Facades\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Ship
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
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $shortName
 * @property-read \App\Supplier|null $supplier
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization forParentable($type, $id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship inShipGroup($id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereParentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereParentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship wherePricingLogicTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereShortNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Ship whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Review[] $reviews
 */
class Ship extends Organization
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organizations';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('ship', function (Builder $builder) {
            $builder->where('type_taxonomy_id', Config::getOrFail('taxonomies.organization_types.ship.id'));
        });
    }

    public function getMorphClass(): string
    {
        return Organization::class;
    }

    /**
     * Relation to parent organization group
     * 
     * @return MorphTo
     */
    public function shipGroup(): MorphTo
    {
        return $this->parentable();
    }

    /**
     * Scope a query to only include ...
     *
     * @param Builder $query query to scope to
     * @param int $id
     * @return Builder
     */
    public function scopeInShipGroup(Builder $query, int $id): Builder
    {
        return $this->scopeForParentable($query, ShipGroup::class, $id);
    }
}
