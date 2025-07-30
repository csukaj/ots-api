<?php

namespace App;

use App\Traits\ModelTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\ModelMealPlan
 *
 * @property int $id
 * @property string $meal_planable_type
 * @property int $meal_planable_id
 * @property int $meal_plan_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property int $date_range_id
 * @property-read DateRange $dateRange
 * @property-read MealPlan $mealPlan
 * @property-read Organization $organization
 * @property-read Collection|Price[] $prices
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $mealPlanable
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PriceElement[] $priceElements
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan forMealPlanable($type, $id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\ModelMealPlan onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan whereDateRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan whereMealPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan whereMealPlanableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan whereMealPlanableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ModelMealPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ModelMealPlan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\ModelMealPlan withoutTrashed()
 */
class ModelMealPlan extends Model
{

    use SoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['meal_plan_id', 'meal_planable_type', 'meal_planable_id', 'date_range_id'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['mealPlanable'];

    /**
     * Relation to mealplan
     *
     * @return HasOne
     */
    public function mealPlan(): HasOne
    {
        return $this->hasOne(MealPlan::class, 'id', 'meal_plan_id')->with('name');
    }

    /**
     * Relation to organization
     *
     * @return MorphTo
     */
    public function mealPlanable(): MorphTo
    {
        return $this->morphTo('meal_planable');
    }

    /**
     * Relation to dateRange
     *
     * @return HasOne
     */
    public function dateRange(): HasOne
    {
        return $this->hasOne(DateRange::class, 'id', 'date_range_id');
    }

    /**
     * Relation to prices
     *
     * @return BelongsToMany
     */
    public function priceElements(): BelongsToMany
    {
        return $this->belongsToMany(PriceElement::class);
    }

    /**
     * Scope a query to only include ...
     *
     * @param Builder $query query to scope to
     * @param string $type
     * @param int $id
     * @return Builder
     */
    public function scopeForMealPlanable(Builder $query, string $type, int $id): Builder
    {
        return $query
            ->where('meal_planable_type', $type)
            ->where('meal_planable_id', $id);
    }

    /**
     * Finds orgnaizationmealplan by name, organization and date range
     * @param string $name
     * @param DateRange $dateRange
     * @return ModelMealPlan
     */
    public static function findByName(string $name, DateRange $dateRange): self {
        $mealPlan = MealPlan::findByName($name);
        return self::where([
            'meal_plan_id' => $mealPlan->id,
            'meal_planable_type' => $dateRange->date_rangeable_type,
            'meal_planable_id' => $dateRange->date_rangeable_id,
            'date_range_id' => $dateRange->id
        ])->firstOrFail();
    }

    /**
     * get names of specified ModelMealPlans list items (in default language) ordered by priority
     *
     * @param type $modelMealPlans
     * @param bool $indexedWithId Is list indexed with ModelMealPlan id
     * @return array<string>
     * @throws Exception
     */
    public static function getNames($modelMealPlans, bool $indexedWithId = false): array
    {
        $return = [];
        $indexedMealPlans = [];

        foreach ($modelMealPlans as $modelMealPlan) {
            if (!is_object($modelMealPlan) || !($modelMealPlan instanceof self)) {
                throw new Exception('Invalid type, expected ModelMealPlan.');
            }
            if ($indexedWithId) {
                $indexedMealPlans[$modelMealPlan->id] = $modelMealPlan;
            } else {
                $indexedMealPlans[] = $modelMealPlan;
            }
        }

        if ($indexedWithId) {
            uasort($indexedMealPlans, array('self', 'compareByPriority'));
        } else {
            usort($indexedMealPlans, array('self', 'compareByPriority'));
        }

        foreach ($indexedMealPlans as $key => $modelMealPlan) {
            $return[$key] = $modelMealPlan->mealPlan->name->name;
        }

        return $return;
    }

    /**
     * Compare function for ModelMealPlans by priority attributa
     * @param ModelMealPlan $a
     * @param ModelMealPlan $b
     * @return int
     * @throws Exception
     */
    public static function compareByPriority($a, $b): int
    {
        if (!is_object($a) || !($a instanceof self)) {
            throw new Exception('Invalid type, expected ModelMealPlan.');
        }
        if (!is_object($b) || !($b instanceof self)) {
            throw new Exception('Invalid type, expected ModelMealPlan.');
        }

        return $a->mealPlan->name->priority <=> $b->mealPlan->name->priority;
    }
}
