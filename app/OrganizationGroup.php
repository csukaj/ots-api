<?php

namespace App;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylerstaxonomy\Entities\ClassificableTrait;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\OrganizationGroup
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Product[] $products
 * @property-read \App\Supplier|null $supplier
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroup onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup wherePricingLogicTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroup withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationGroup withoutTrashed()
 */
class OrganizationGroup extends Model
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
        'type_taxonomy_id',
        'is_active',
        'parent_id',
        'pricing_logic_taxonomy_id',
        'margin_type_taxonomy_id',
        'margin_value',
        'created_at',
        'updated_at',
        'deleted_at',
        'location_id',
        'supplier_id'
    ];

    protected $cascadeDeletes = [
        'name',
        'classifications',
        'metas',
        'descriptions',
        'devices',
        'ageRanges',
        'dateRanges',
        'galleries'
    ];

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
     * Relation to type taxonomy
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    /**
     * Relation to type taxonomy
     *
     * @return MorphMany
     */
    public function children(): MorphMany
    {
        return $this->morphMany(Organization::class, 'parentable');
    }

    /**
     * Relation to parent organization
     *
     * @return HasOne
     */
    public function parentOrganization(): HasOne
    {
        return $this->hasOne(Organization::class, 'id', 'parent_id');
    }

    /**
     * Relation to organization classifications
     *
     * @return HasMany
     */
    public function classifications(): HasMany
    {
        return $this->hasMany(OrganizationGroupClassification::class, 'organization_group_id', 'id');
    }

    /**
     * Relation to Organization Metas
     *
     * @return HasMany
     */
    public function metas(): HasMany
    {
        return $this->hasMany(OrganizationGroupMeta::class, 'organization_group_id', 'id');
    }

    /**
     * Relation to Organization Descriptions
     *
     * @return HasMany
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(OrganizationGroupDescription::class, 'organization_group_id', 'id');
    }

    /**
     * Relation to devices
     *
     * @return MorphMany
     */
    public function devices(): MorphMany
    {
        return $this->morphMany(Device::class, 'deviceable')->orderBy('id');
    }

    /**
     * Relation to organization age ranges
     *
     * @return MorphMany
     */
    public function ageRanges(): MorphMany
    {
        return $this->morphMany(AgeRange::class, 'age_rangeable');
    }

    /**
     * Relation to organization date ranges
     *
     * @return MorphMany
     */
    public function dateRanges(): MorphMany
    {
        return $this->morphMany(DateRange::class, 'date_rangeable');
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
     * Relation to organization mealplans
     *
     * @return MorphMany
     */
    public function modelMealPlans(): MorphMany
    {
        return $this->morphMany(ModelMealPlan::class, 'meal_planable');
    }

    /**
     * Relation to organization managers
     *
     * @return HasMany
     */
    public function managers(): HasMany
    {
        return $this->hasMany(OrganizationManager::class, 'organization_id', 'id');
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
     * Relation to products
     *
     * @return MorphMany
     */
    public function products(): MorphMany
    {
        return $this->morphMany(Product::class, 'productable');
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
     * Get availability mode associated to this organization group
     *
     * @return OrganizationGroupClassification|null
     */
    public function getAvailabilityMode()
    {
        return $this->getClassification(Config::get('taxonomies.organization_group_properties.categories.settings.items.availability_mode.id'));
    }

    /**
     * Get last update time of organization table
     *
     * @return mixed
     * @static
     */
    static public function getLastUpdate()
    {
        return DB::table('organization_groups')->max('updated_at');
    }

    /**
     * get Last NameUpdate Timestamp for organizations
     * @return int
     */
    static public function getLastNameUpdateTimestamp(): int
    {
        $maximums = DB::table('organization_groups')
            ->select(DB::raw('max(organization_groups.updated_at) as "organizations_max", max(descriptions.updated_at) as "description_max", max(description_translations.updated_at) as "description_translations_max"'))
            ->join('descriptions', 'organization_groups.name_description_id', '=', 'descriptions.id')
            ->leftJoin('description_translations', 'descriptions.id', '=', 'description_translations.description_id')
            ->first();
        return max(strtotime($maximums->organizations_max), strtotime($maximums->description_max),
            strtotime($maximums->description_translations_max));
    }

    /**
     * Get name list of accommodations on default language
     *
     * @return Collection
     * @static
     */
    static public function getEnglishNamesByType($typeTxId = null)
    {
        $query = self
            ::select(DB::raw("organization_groups.id, descriptions.description"))
            ->join('descriptions', 'organization_groups.name_description_id', '=', 'descriptions.id')
            ->whereNull('descriptions.deleted_at');
        if ($typeTxId) {
            $query->where('type_taxonomy_id', $typeTxId);
        }
        return $query->get();
    }

    /**
     * Get name list of organization on every languages (for type of caller class)
     *
     * @param string $nameToFind
     * @param int $ignoreId
     * @return Collection
     */
    static public function getNames($nameToFind = null, $ignoreId = null)
    {

        $defaultLangCode = Language::getDefault()->iso_code;

        $defaultLangNames = static
            ::select(DB::raw("organization_groups.id, descriptions.description, '{$defaultLangCode}' AS language"))
            ->join('descriptions', 'organization_groups.name_description_id', '=', 'descriptions.id')
            ->whereNull('descriptions.deleted_at');
        if (!empty($nameToFind)) {
            if (is_array($nameToFind)) {
                $defaultLangNames->whereIn('descriptions.description', $nameToFind);
            } else {
                $defaultLangNames->where('descriptions.description', '=', $nameToFind);
            }
        }
        if (!empty($ignoreId)) {
            $defaultLangNames->where('organization_groups.id', '!=', $ignoreId);
        }

        $translationNames = static
            ::select([
                'organization_groups.id',
                'description_translations.description',
                'languages.iso_code AS language'
            ])
            ->join('descriptions', 'organization_groups.name_description_id', '=', 'descriptions.id')
            ->join('description_translations', 'descriptions.id', '=', 'description_translations.description_id')
            ->join('languages', 'description_translations.language_id', '=', 'languages.id')
            ->whereNull('descriptions.deleted_at')
            ->whereNull('description_translations.deleted_at');
        if (!empty($nameToFind)) {
            if (is_array($nameToFind)) {
                $translationNames->whereIn('description_translations.description', $nameToFind);
            } else {
                $translationNames->where('description_translations.description', '=', $nameToFind);
            }
        }
        if (!empty($ignoreId)) {
            $translationNames->where('organization_groups.id', '!=', $ignoreId);
        }

        return $translationNames->union($defaultLangNames)->get();
    }
}
