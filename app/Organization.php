<?php
namespace App;

use App\Facades\Config;
use Carbon\Carbon;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylerstaxonomy\Entities\ClassificableTrait;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Language;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Organization
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Review[] $reviews
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $shortName
 * @property-read \App\Supplier|null $supplier
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization forParentable($type, $id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Organization onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereParentableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereParentableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization wherePricingLogicTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereShortNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Organization whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Organization withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Organization withoutTrashed()
 * @mixin \Eloquent
 */
class Organization extends Model
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
        'parentable_type',
        'parentable_id',
        'pricing_logic_taxonomy_id',
        'margin_type_taxonomy_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'location_id',
        'supplier_id'
    ];

    protected $cascadeDeletes = ['name','classifications','metas', 'descriptions', 'devices', 'ageRanges', 'dateRanges', 'galleries', 'managers', 'modelMealPlans', 'reviews'];

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
     * Relation to name description
     *
     * @return HasOne
     */
    public function shortName(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'short_name_description_id');
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
     * Get all of the owning parentable models.
     * @return MorphTo
     */
    public function parentable(): MorphTo
    {
        return $this->morphTo();
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
     * @return MorphTo
     */
    public function parentOrganization(): MorphTo
    {
        return $this->parentable();
    }

    /**
     * Relation to parent organization group
     *
     * @return MorphTo
     */
    public function organizationGroup(): MorphTo
    {
        return $this->parentable();
    }

    /**
     * Relation to organization classifications
     *
     * @return HasMany
     */
    public function classifications(): HasMany
    {
        return $this->hasMany(OrganizationClassification::class, 'organization_id', 'id');
    }

    /**
     * Relation to Organization Metas
     *
     * @return HasMany
     */
    public function metas(): HasMany
    {
        return $this->hasMany(OrganizationMeta::class, 'organization_id', 'id');
    }

    /**
     * Relation to Organization Descriptions
     *
     * @return HasMany
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(OrganizationDescription::class, 'organization_id', 'id');
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
     * Relation to supplier
     *
     * @return BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'review_subject')->orderBy('id');
    }

    /**
     * Scope a query to only include ...
     *
     * @param Builder $query query to scope to
     * @param string $type
     * @param int $id
     * @return Builder
     */
    public function scopeForParentable(Builder $query, string $type, int $id): Builder
    {
        return $query
                ->where('parentable_type', $type)
                ->where('parentable_id', $id);
    }

    /**
     * Get availability mode associated to this organization
     *
     * @return OrganizationClassification|null
     */
    public function getAvailabilityMode()
    {
        return $this->getClassification(Config::get('taxonomies.organization_properties.categories.settings.items.availability_mode.id'));
    }

    /**
     * Get last update time of organization table
     *
     * @return mixed
     * @static
     */
    static public function getLastUpdate()
    {
        return DB::table('organizations')->max('updated_at');
    }

    /**
     * get Last NameUpdate Timestamp for organizations
     * @return int
     */
    static public function getLastNameUpdateTimestamp(): int
    {
        $maximums = DB::table('organizations')
            ->select(DB::raw('max(organizations.updated_at) as "organizations_max", max(descriptions.updated_at) as "description_max"'))
            ->join('descriptions', 'organizations.name_description_id', '=', 'descriptions.id')
            ->first();
        //description translations not needed. It touches (parent) description when updated
        return max(strtotime($maximums->organizations_max), strtotime($maximums->description_max));
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
            ::select(DB::raw("organizations.id, descriptions.description"))
            ->join('descriptions', 'organizations.name_description_id', '=', 'descriptions.id')
            ->whereNull('descriptions.deleted_at')
            ->orderBy('descriptions.description');
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
            ::select(DB::raw("organizations.id, descriptions.description, '" . $defaultLangCode . "' AS language"))
            ->join('descriptions', 'organizations.name_description_id', '=', 'descriptions.id')
            ->whereNull('descriptions.deleted_at');
        if (!empty($nameToFind)) {
            if (is_array($nameToFind)) {
                $defaultLangNames->whereIn('descriptions.description', $nameToFind);
            } else {
                $defaultLangNames->where('descriptions.description', '=', $nameToFind);
            }
        }
        if (!empty($ignoreId)) {
            $defaultLangNames->where('organizations.id', '!=', $ignoreId);
        }

        $translationNames = static
            ::select(['organizations.id', 'description_translations.description', 'languages.iso_code AS language'])
            ->join('descriptions', 'organizations.name_description_id', '=', 'descriptions.id')
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
            $translationNames->where('organizations.id', '!=', $ignoreId);
        }

        return $translationNames->union($defaultLangNames)->get();
    }

    /**
     * @param int $channelManagerTaxonomyId
     * @throws \Exception
     */
    static public function getChannelManagedOrganizationIds(int $channelManagerTaxonomyId)
    {
        $channelManagerTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.items.channel_manager.id');
        $channelManagerHotelIdTxId = Config::getOrFail('taxonomies.organization_properties.categories.settings.metas.channel_manager_id.id');
        $hasChannelManager = OrganizationClassification
            ::where('classification_taxonomy_id', $channelManagerTxId)
            ->where('value_taxonomy_id', $channelManagerTaxonomyId)
            ->pluck('organization_id');
        $hasHotelId = OrganizationMeta
            ::where('taxonomy_id', $channelManagerHotelIdTxId)
            ->pluck('value','organization_id')->filter()->keys();
        return $hasChannelManager->intersect($hasHotelId)->toArray();
    }
}
