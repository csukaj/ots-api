<?php

namespace App;

use App\Facades\Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Stylerscontact\Entities\Contact;
use Modules\Stylerscontact\Entities\Person;

/**
 * App\Supplier
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Accommodation[] $accommodations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\AgeRange[] $ageRanges
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Organization[] $children
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OrganizationClassification[] $classifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylerscontact\Entities\Contact[] $contacts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Cruise[] $cruises
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylerscontact\Entities\Person[] $people
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $pricingLogic
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShipGroup[] $shipGroups
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $shortName
 * @property-read \App\Supplier|null $supplier
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization forParentable($type, $id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereParentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereParentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier wherePricingLogicTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereShortNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Supplier whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Supplier|null $organizationGroup
 */
class Supplier extends Organization
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

        static::addGlobalScope('supplier', function (Builder $builder) {
            $builder->where('type_taxonomy_id', Config::getOrFail('taxonomies.organization_types.supplier.id'));
        });
    }

    public function getMorphClass()
    {
        return Organization::class;
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
    }

    public function shipGroups(): HasMany
    {
        return $this->hasMany(ShipGroup::class);
    }

    public function cruises(): HasMany
    {
        return $this->hasMany(Cruise::class);
    }

    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function people(): MorphMany
    {
        return $this->morphMany(Person::class, 'personable');
    }

}
