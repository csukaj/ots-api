<?php

namespace App;

use App\Facades\Config;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\PriceModifier
 *
 * @property int $id
 * @property int $name_description_id
 * @property int $modifier_type_taxonomy_id
 * @property int $condition_taxonomy_id
 * @property int $offer_taxonomy_id
 * @property int $description_description_id
 * @property int $priority
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property string $promo_code
 * @property bool $is_active
 * @property bool $is_annual
 * @property-read Description $description
 * @property-read Collection|PriceModifierClassification[] $priceModifierClassifications
 * @property-read Collection|PriceModifierMeta[] $priceModifierMetas
 * @property-read Collection|PriceModifierPeriod[] $priceModifierPeriods
 * @property-read Description $name
 * @property-read Taxonomy $offer
 * @property-read Collection|OfferClassification[] $offerClassifications
 * @property-read Collection|OfferMeta[] $offerMetas
 * @property-read Taxonomy $type
 * @mixin \Eloquent
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $condition
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DateRange[] $dateRanges
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $modifierType
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier discount()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier forModel($modelType, $modelId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier forType($type)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifier onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier orderByName()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereConditionTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereDescriptionDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereIsAnnual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereModifierTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereOfferTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier wherePromoCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifier withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifier withoutTrashed()
 */
class PriceModifier extends Model
{

    use SoftDeletes, CascadeSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name_description_id',
        'description_description_id',
        'modifier_type_taxonomy_id',
        'condition_taxonomy_id',
        'offer_taxonomy_id',
        'is_active',
        'is_annual',
        'priority',
        'promo_code'
    ];

    protected $cascadeDeletes = [
        'name',
        'description',
        'offerMetas',
        'offerClassifications',
        'priceModifierMetas',
        'priceModifierClassifications'
    ];

    /**
     * Relation to name Description
     *
     * @return HasOne
     */
    public function name(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'name_description_id');
    }

    /**
     * Relation to description Description
     *
     * @return HasOne
     */
    public function description(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'description_description_id');
    }

    /**
     * Relation to type taxonomy
     *
     * @return HasOne
     */
    public function modifierType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'modifier_type_taxonomy_id');
    }

    /**
     * Relation to condition type taxonomy
     *
     * @return HasOne
     */
    public function condition(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'condition_taxonomy_id');
    }

    /**
     * Relation to offer taxonomy
     *
     * @return HasOne
     */
    public function offer(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'offer_taxonomy_id');
    }

    /**
     * Relation to PriceModifierPeriod
     *
     * @return HasMany
     */
    public function priceModifierPeriods(): HasMany
    {
        return $this->hasMany(PriceModifierPeriod::class, 'price_modifier_id', 'id');
    }

    /**
     * Relation to Date ranges through PriceModifierPeriods
     *
     * @return HasManyThrough
     */
    public function dateRanges()
    {
        return $this->belongsToMany(DateRange::class, 'price_modifier_periods', 'price_modifier_id', 'date_range_id')
            ->whereNull('price_modifier_periods.deleted_at');
    }

    /**
     * Relation to offer metas
     *
     * @return HasMany
     */
    public function offerMetas(): HasMany
    {
        return $this->hasMany(OfferMeta::class, 'price_modifier_id', 'id');
    }

    /**
     * Relation to Offer Classifications
     *
     * @return HasMany
     */
    public function offerClassifications(): HasMany
    {
        return $this->hasMany(OfferClassification::class, 'price_modifier_id', 'id');
    }

    /**
     * Relation to PriceModifier Metas
     *
     * @return HasMany
     */
    public function priceModifierMetas(): HasMany
    {
        return $this->hasMany(PriceModifierMeta::class, 'price_modifier_id', 'id');
    }

    /**
     * Relation to PriceModifier Classifications
     *
     * @return HasMany
     */
    public function priceModifierClassifications(): HasMany
    {
        return $this->hasMany(PriceModifierClassification::class, 'price_modifier_id', 'id');
    }

    /**
     * Scope a query to only include PriceModifiers for specified organization.
     *
     * @param Builder $query query to scope to
     * @param string $modelType
     * @param int $modelId
     * @return Builder
     */
    public function scopeForModel(Builder $query, string $modelType, int $modelId): Builder
    {
        return $query->whereIn('id', self::getModelPriceModifierIds($modelType, $modelId));
    }

    public function scopeDiscount(Builder $query): Builder
    {
        return $this->scopeForType('discount');
    }

    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('modifier_type_taxonomy_id',
            Config::getOrFail('taxonomies.price_modifier_types.') . $type);
    }

    /**
     * Scope a query to be ordered by Price Modifier name.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopeOrderByName(Builder $query): Builder
    {
        return $query->orderByRaw('(SELECT description FROM descriptions WHERE descriptions.id = price_modifiers.name_description_id) ASC');
    }

    /**
     * get model Id for this Price Modifier
     * @return mixed
     */
    public function getPricemodifiableModel()
    {
        $dateRanges = $this->dateRanges;
        return count($dateRanges) ? $this->dateRanges[0]->dateRangeable : null;
    }

    /**
     * Find sibling Price Modifiers (= in same organization)
     * sorted by the special Price Modifier ordering
     * @see PriceModifier::sortbyPriority()
     *
     * @param bool $withSelf Include self in list?
     * @return Collection
     */
    public function findSiblingsInOrder(bool $withSelf = false): Collection
    {
        $model = $this->getPricemodifiableModel();
        $q = self::forModel(get_class($model), $model->id)->orderBy('priority');
        if (!$withSelf) {
            $q->where('id', '!=', $this->id);
        }
        return self::sortbyPriority($q->get());
    }

    public function touchModel()
    {
        $this->getPricemodifiableModel()->touch();
    }

    /**
     * Custom Price Modifier sorter function
     * It sorts by offer type (first price row, then free night, then other)
     * after thet is sorts by priority
     *
     * @param Collection $priceModifier
     * @return Collection
     */
    static public function sortbyPriority(Collection $priceModifier): Collection
    {
        return $priceModifier->sort(function (PriceModifier $a, PriceModifier $b) {
            $offerPriority = [
                Config::get('taxonomies.price_modifier_offers.price_row.id') => 0,
                Config::get('taxonomies.price_modifier_offers.free_nights.id') => 1
            ];
            $priority = intval($a->priority) - intval($b->priority);
            if (!isset($offerPriority[$a->offer_taxonomy_id]) && !isset($offerPriority[$b->offer_taxonomy_id])) {
                return $priority;
            } elseif (!isset($offerPriority[$a->offer_taxonomy_id])) {
                return 1;
            } elseif (!isset($offerPriority[$b->offer_taxonomy_id])) {
                return -1;
            }
            return ($offerPriority[$a->offer_taxonomy_id] - $offerPriority[$b->offer_taxonomy_id]) ?: $priority;
        }
        )->values();
    }

    /**
     * get model's PriceModifier Ids
     *
     * @param string $modelType
     * @param int $modelId
     * @return array
     * @static
     */
    static public function getModelPriceModifierIds(string $modelType, int $modelId): array
    {
        return PriceModifierPeriod
            ::join('date_ranges', 'price_modifier_periods.date_range_id', '=', 'date_ranges.id')
            ->join('price_modifiers', 'price_modifier_periods.price_modifier_id', '=', 'price_modifiers.id')
            ->where('date_ranges.date_rangeable_type', $modelType)
            ->where('date_ranges.date_rangeable_id', $modelId)
            ->whereNull('date_ranges.deleted_at')
            ->whereNull('price_modifiers.deleted_at')
            ->pluck('price_modifier_periods.price_modifier_id')
            ->unique()
            ->toArray();
    }

    /**
     * Find Price Modifier by name in a date rangeable model
     * (Yet used only at unit tests)
     *
     * @param string $name
     * @param string $modelType
     * @param int $modelId
     * @return PriceModifier
     * @throws ModelNotFoundException
     */
    static public function findByName(string $name, string $modelType, int $modelId): PriceModifier
    {
        $priceModifier = self
            ::select('price_modifiers.*')
            ->join('descriptions', 'price_modifiers.name_description_id', '=', 'descriptions.id')
            ->whereIn('price_modifiers.id', self::getModelPriceModifierIds($modelType, $modelId))
            ->where('descriptions.description', $name)
            ->whereNull('descriptions.deleted_at')
            ->first();
        if (is_null($priceModifier)) {
            throw new ModelNotFoundException("Price modifier with name '{$name}' not found in `{$modelType}` #{$modelId}.");
        }
        return $priceModifier;
    }
}
