<?php
namespace App;

use App\Exceptions\UserException;
use App\Traits\ModelTrait;
use Carbon\Carbon;
use DateTime;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * App\DateRange
 *
 * @property int $id
 * @property int $name_description_id
 * @property int $date_rangeable_id
 * @property string $date_rangeable_type
 * @property string $from_time
 * @property string $to_time
 * @property int $type_taxonomy_id
 * @property int $margin_type_taxonomy_id
 * @property float $margin_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property int $minimum_nights
 * @property-read Collection|DeviceMinimumNights[] $deviceMinimumNights
 * @property-read Collection|PriceModifierPeriod[] $priceModifierPeriod
 * @property-read Taxonomy $marginType
 * @property-read Description $name
 * @property-read Organization $organization
 * @property-read Collection|ModelMealPlan[] $modelMealPlans
 * @property-read Collection|Price[] $prices
 * @property-read Taxonomy $type
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $dateRangeable
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PriceModifierPeriod[] $priceModifierPeriods
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange closed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange forDateRangeable($type, $id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\DateRange onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange open()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange priceModifier()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereDateRangeableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereDateRangeableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereFromTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereMinimumNights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereToTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DateRange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\DateRange withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\DateRange withoutTrashed()
 */
class DateRange extends Model
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
        'name_description_id',
        'date_rangeable_type',
        'date_rangeable_id',
        'from_time',
        'to_time',
        'type_taxonomy_id',
        'margin_value',
        'minimum_nights'
    ];

    protected $cascadeDeletes = ['name', 'deviceMinimumNights'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['dateRangeable'];

    /**
     * Save the model to the database.
     * Sets margin_type_taxonomy_id of organization and checks for overlap
     *
     * @param  array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if ($this->date_rangeable_type == Organization::class && !empty($this->date_rangeable_id)) {
            $this->margin_type_taxonomy_id = Organization::findOrFail($this->date_rangeable_id)->margin_type_taxonomy_id;
        } else {
            $this->margin_type_taxonomy_id = null;
        }

        if (
            self::hasDateRanges($this->date_rangeable_type, $this->date_rangeable_id, $this->from_time, $this->to_time, $this->id, $this->type_taxonomy_id) && ($this->type_taxonomy_id != Config::get('taxonomies.date_range_types.price_modifier'))
        ) {
            throw new UserException('Date range overlap!');
        }

        return parent::save($options);
    }

    /**
     * Relation to name description
     *
     * @return HasOne
     */
    public function name()
    {
        return $this->hasOne(Description::class, 'id', 'name_description_id');
    }

    /**
     * Polymorphic relation to cruise and organization
     *
     * @return MorphTo
     */
    public function dateRangeable()
    {
        return $this->morphTo();
    }

    /**
     * Relation to organization MealPlans
     *
     * @return HasMany
     */
    public function modelMealPlans(): HasMany
    {
        return $this->hasMany(ModelMealPlan::class, 'date_range_id', 'id');
    }

    /**
     * Relation to type taxonomy
     *
     * @return HasOne
     */
    public function type()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    /**
     * Relation to margin type taxonomy
     *
     * @return HasOne
     */
    public function marginType()
    {
        return $this->hasOne(Taxonomy::class, 'id', 'margin_type_taxonomy_id');
    }

    /**
     * Relation to price modifier periods
     *
     * @return HasMany
     */
    public function priceModifierPeriods()
    {
        return $this->hasMany(PriceModifierPeriod::class, 'date_range_id', 'id');
    }

    /**
     * Relation to DeviceMinimumNights
     *
     * @return HasMany
     */
    public function deviceMinimumNights()
    {
        return $this->hasMany(DeviceMinimumNights::class, 'date_range_id', 'id');
    }

    /**
     * Scope a query to only include open date ranges.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopeOpen($query): Builder
    {
        return $query->where('type_taxonomy_id', Config::get('taxonomies.date_range_types.open'));
    }

    /**
     * Scope a query to only include closed date ranges.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopeClosed($query): Builder
    {
        return $query->where('type_taxonomy_id', Config::get('taxonomies.date_range_types.closed'));
    }

    /**
     * Scope a query to only include price modifier date ranges.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopePriceModifier($query): Builder
    {
        return $query->where('type_taxonomy_id', Config::get('taxonomies.date_range_types.price_modifier'));
    }

    /**
     * Scope a query to only include date ranges for a specific type.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopeForDateRangeable(Builder $query, string $type, int $id): Builder
    {
        return $query
                ->where('date_rangeable_type', $type)
                ->where('date_rangeable_id', $id);
    }

    /**
     * set FromTime Attribute with separation time
     *
     * @param string $date
     */
    public function setFromTimeAttribute(string $date)
    {
        $this->attributes['from_time'] = $this->getSeparationTime($date, true);
    }

    /**
     * get FromTime Attribute from separated time
     *
     * @param string $time
     * @return string
     */
    public function getFromTimeAttribute(string $time): string
    {
        return substr($time, 0, 10);
    }

    /**
     * set toTime Attribute with separation time
     *
     * @param string $date
     */
    public function setToTimeAttribute(string $date)
    {
        $this->attributes['to_time'] = $this->getSeparationTime($date, false);
    }

    /**
     * get toTime Attribute from separated time
     *
     * @param string $time
     * @return string
     */
    public function getToTimeAttribute(string $time): string
    {
        return $time ? substr($time, 0, 10) : null;
    }

    /**
     * Creates or deletes organization meal plans
     * @param array $mealPlanNames
     * @return bool
     */
    public function setMealPlans(array $mealPlanNames): bool
    {
        $oldModelMealPlanNames = ModelMealPlan::getNames($this->modelMealPlans, true);

        foreach ($mealPlanNames as $mealPlanName) {
            if (in_array($mealPlanName, $oldModelMealPlanNames)) {
                unset($oldModelMealPlanNames[array_search($mealPlanName, $oldModelMealPlanNames)]);
            } elseif (!isset(Config::get('taxonomies.meal_plans')[$mealPlanName])) {
                throw new UserException("Invalid meal plan name: `{$mealPlanName}`");
            } else {
                (new ModelMealPlan())->createOrRestore([
                'meal_plan_id' => Config::get('taxonomies.meal_plans')[$mealPlanName]['meal_plan_id'],
                'meal_planable_type' => $this->date_rangeable_type,
                'meal_planable_id' => $this->date_rangeable_id,
                'date_range_id' => $this->id
                ]);
            }
        }
        PriceElement::where('date_range_id', '=', $this->id)
            ->whereIn('model_meal_plan_id', array_keys($oldModelMealPlanNames))
            ->delete();
        (new ModelMealPlan())->destroy(array_keys($oldModelMealPlanNames));

        $this->load('modelMealPlans');

        return true;
    }

    /**
     * Get number of overlapping days of two dates
     *
     * @param string $fromDate
     * @param string $toDate
     * @return int
     */
    public function getOverlapDays(string $fromDate, string $toDate): int
    {
        $queryFrom = self::formatTime($fromDate, true);
        $queryTo = self::formatTime($toDate, false);
        $objectFrom = self::formatTime($this->from_time, true);
        $objectTo = self::formatTime($this->to_time, false)->modify('+1 day');

        if ($queryTo < $objectFrom || $queryFrom > $objectTo) {
            return 0;
        }

        $countFrom = max([$queryFrom, $objectFrom]);
        $countTo = min([$queryTo, $objectTo]);

        return (int) $countTo->diff($countFrom)->format('%a');
    }

    /**
     * Creates DateTime object from date string
     *
     * @param string $dateOrTime
     * @param bool $isFrom is it from_time?
     * @return DateTime
     */
    static public function formatTime($dateOrTime, bool $isFrom)
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', self::getSeparationTime($dateOrTime, $isFrom));
    }

    /**
     * Adds separation time to a date
     *
     * @param string $dateOrTime
     * @param bool $isFrom
     * @return string
     */
    static public function getSeparationTime(string $dateOrTime, bool $isFrom): string
    {
        if (is_null($dateOrTime) || strlen($dateOrTime) > 10) {
            return $dateOrTime;
        }
        return $dateOrTime . ' ' . ($isFrom ? '0:00:00' : '23:59:59');
    }

    /**
     * get DateRanges In Interval by specified parameters
     *
     * @param string $dateRangeableType organization
     * @param int $dateRangeableId organization
     * @param string $fromDate date from
     * @param string $toDate date to
     * @param int $typeTxId type taxonomy id
     * @param int $ignoreId date range ids to ignore
     * @return Collection
     * @throws UserException
     * @static
     */
    static public function getDateRangesInInterval(
    string $dateRangeableType, int $dateRangeableId, string $fromDate, string $toDate, int $typeTxId, int $ignoreId = null
    ): Collection
    {

        if (is_null($dateRangeableType)) {
            throw new UserException('Invalid argument for accommodation type.');
        }
        if (is_null($dateRangeableId)) {
            throw new UserException('Invalid argument for accommodation id.');
        }
        if (is_null($fromDate)) {
            throw new UserException('Invalid argument for from date.');
        }
        if (is_null($toDate)) {
            throw new UserException('Invalid argument for to date.');
        }
        if (is_null($typeTxId)) {
            throw new UserException('Invalid argument for type.');
        }

        $fromTime = self::getSeparationTime($fromDate, true);
        $toTime = self::getSeparationTime($toDate, false);

        $query1 = DateRange
            ::where('date_rangeable_type', '=', $dateRangeableType)
            ->where('date_rangeable_id', '=', $dateRangeableId)
            ->where('from_time', '<=', $fromTime)
            ->where('to_time', '>=', $toTime)
            ->where('type_taxonomy_id', '=', $typeTxId);
        if ($ignoreId) {
            $query1->where('id', '!=', $ignoreId);
        }

        $query2 = DateRange
            ::where('date_rangeable_type', '=', $dateRangeableType)
            ->where('date_rangeable_id', '=', $dateRangeableId)
            ->where('from_time', '>=', $fromTime)
            ->where('from_time', '<=', $toTime)
            ->where('type_taxonomy_id', '=', $typeTxId);
        if ($ignoreId) {
            $query2->where('id', '!=', $ignoreId);
        }

        $query3 = DateRange
            ::where('date_rangeable_type', '=', $dateRangeableType)
            ->where('date_rangeable_id', '=', $dateRangeableId)
            ->where('to_time', '>=', $fromTime)
            ->where('to_time', '<=', $toTime)
            ->where('type_taxonomy_id', '=', $typeTxId);
        if ($ignoreId) {
            $query3->where('id', '!=', $ignoreId);
        }

        return $query3->union($query2)->union($query1)->orderBy('from_time')->get();
    }

    /**
     * Has any date range by specified parameters
     *
     * @param string $dateRangeableType
     * @param int $dateRangeableId
     * @param string $fromTime
     * @param string $toTime
     * @param int $ignoreId
     * @param int $typeTaxonomyId
     * @return bool
     * @static
     *
     * @todo SoftDelete
     */
    static public function hasDateRanges(
    string $dateRangeableType, $dateRangeableId, $fromTime = null, $toTime = null, $ignoreId = null, $typeTaxonomyId = null
    ): bool
    {
        $query = DB::table('date_ranges')
            ->select('id')
            ->where('date_rangeable_type', $dateRangeableType)
            ->where('date_rangeable_id', '=', $dateRangeableId)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($fromTime, $toTime) {
            if (!is_null($fromTime)) {
                $query->orWhere(function ($query) use ($fromTime) {
                    $query->where('from_time', '<=', $fromTime);
                    $query->where('to_time', '>', $fromTime);
                });
            }
            if (!is_null($toTime)) {
                $query->orWhere(function ($query) use ($toTime) {
                    $query->where('from_time', '<', $toTime);
                    $query->where('to_time', '>=', $toTime);
                });
            }
            if (!is_null($fromTime) && !is_null($toTime)) {
                $query->orWhere(function ($query) use ($fromTime, $toTime) {
                    $query->where('from_time', '<', $fromTime);
                    $query->where('to_time', '>=', $toTime);
                });
                $query->orWhere(function ($query) use ($fromTime, $toTime) {
                    $query->where('from_time', '>=', $fromTime);
                    $query->where('to_time', '<', $toTime);
                });
            }
        });

        if (!is_null($ignoreId)) {
            $query->where('id', '!=', $ignoreId);
        }

        if (!is_null($typeTaxonomyId)) {
            $query->where('type_taxonomy_id', '=', $typeTaxonomyId);
        }

        return $query->count() > 0;
    }

    /**
     * Get organization date range by specified data
     *
     * @param string $dateRangeableType
     * @param int $dateRangeableId
     * @param string $fromTime
     * @param string $toTime
     * @param int $typeTxId
     * @param bool $withTrashed Include deleted objects too?
     * @return DateRange|static|null
     */
    static public function getByData(
    string $dateRangeableType, int $dateRangeableId, string $fromTime, string $toTime, int $typeTxId, bool $withTrashed = false
    )
    {
        $query = self::where('date_rangeable_type', '=', $dateRangeableType)
            ->where('date_rangeable_id', '=', $dateRangeableId)
            ->where('from_time', '=', self::getSeparationTime($fromTime, true))
            ->where('to_time', '=', self::getSeparationTime($toTime, false))
            ->where('type_taxonomy_id', '=', $typeTxId);
        if ($withTrashed) {
            $query->withTrashed();
        }
        return $query->first();
    }

    /**
     * Date range setter.
     * Creates or updates date range with specifiad data setting all needed properties.
     *
     * @param string $dateRangeableType
     * @param int $dateRangeableId
     * @param array $dateRangeData
     * @return DateRange
     */
    static public function setByData($dateRangeableType, $dateRangeableId, $dateRangeData): DateRange
    {
        $dateRangeTypeTx = Taxonomy::getTaxonomy($dateRangeData['type'], Config::get('taxonomies.date_range_type'));

        if (!isset($dateRangeData['from_time']) && !empty($dateRangeData['from_date'])) {
            $dateRangeData['from_time'] = self::getSeparationTime($dateRangeData['from_date'], true);
            unset($dateRangeData['from_date']);
        }

        if (!isset($dateRangeData['to_time']) && !empty($dateRangeData['to_date'])) {
            $dateRangeData['to_time'] = self::getSeparationTime($dateRangeData['to_date'], true);
            unset($dateRangeData['to_date']);
        }

        $orgDateRange = null;
        if (!empty($dateRangeData['id'])) {
            $orgDateRange = self::find($dateRangeData['id']);
        }
        if (!$orgDateRange) {
            $orgDateRange = self::getByData($dateRangeableType, $dateRangeableId, $dateRangeData['from_time'], $dateRangeData['to_time'], $dateRangeTypeTx->id);
        }
        if (!$orgDateRange) {
            $orgDateRange = new DateRange();
        }

        if (!empty($dateRangeData['name'])) {
            $dateRangeNameDescription = (new DescriptionSetter($dateRangeData['name'], $orgDateRange->name_description_id))
                ->set();
        } else {
            $dateRangeNameDescription = null;
        }

        $orgDateRange->fill([
            'name_description_id' => $dateRangeNameDescription ? $dateRangeNameDescription->id : null,
            'date_rangeable_type' => $dateRangeableType,
            'date_rangeable_id' => $dateRangeableId,
            'from_time' => $dateRangeData['from_time'],
            'to_time' => $dateRangeData['to_time'],
            'type_taxonomy_id' => $dateRangeTypeTx->id,
            'minimum_nights' => isset($dateRangeData['minimum_nights']) ? $dateRangeData['minimum_nights'] : null
        ]);

        if (isset($dateRangeData['margin_value'])) {
            $orgDateRange->margin_value = $dateRangeData['margin_value'];
        }

        $orgDateRange->saveOrFail();

        return $orgDateRange;
    }
}
