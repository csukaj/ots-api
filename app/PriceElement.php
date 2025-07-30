<?php

namespace App;

use App\Manipulators\PriceCalculator;
use App\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\PriceElement
 *
 * @property int $id
 * @property int $price_id
 * @property int $model_meal_plan_id
 * @property int $date_range_id
 * @property float $net_price
 * @property float $rack_price
 * @property int $margin_type_taxonomy_id
 * @property float $margin_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Taxonomy $marginType
 * @property-read DateRange $sateRange
 * @property-read ModelMealPlan $modelMealPlan
 * @property-read Price $price
 * @mixin \Eloquent
 * @property-read \App\DateRange|null $dateRange
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceElement onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereDateRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereModelMealPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereNetPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement wherePriceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereRackPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceElement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PriceElement withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceElement withoutTrashed()
 */
class PriceElement extends Model {

    use SoftDeletes, ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price_id', 'model_meal_plan_id', 'date_range_id', 'net_price', 'rack_price', 'margin_type_taxonomy_id', 'margin_value'
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['price', 'dateRange'];


    /**
     * Custom save for PriceElement overwriting Model::save()
     * Sets margin type of Date range if assigned
     *
     * @param array $options
     * @return type
     */
    public function save(array $options = []) {
        if (!empty($this->date_range_id)) {
            $this->margin_type_taxonomy_id = DateRange::findOrFail($this->date_range_id)->dateRangeable->margin_type_taxonomy_id;
        } else {
            $this->margin_type_taxonomy_id = null;
        }

        return parent::save($options);
    }

    /**
     * Relation to price
     *
     * @return HasOne
     */
    public function price(): BelongsTo {
        return $this->belongsTo(Price::class);
    }

    /**
     * Relation to ModelMealPlan
     *
     * @return HasOne
     */
    public function modelMealPlan(): HasOne {
        return $this->hasOne(ModelMealPlan::class, 'id', 'model_meal_plan_id');
    }

    /**
     * Relation to dateRange
     *
     * @return HasOne
     */
    public function dateRange(): BelongsTo {
        return $this->belongsTo(DateRange::class);
    }

    /**
     * Relation to marginType
     *
     * @return HasOne
     */
    public function marginType(): HasOne {
        return $this->hasOne(Taxonomy::class, 'id', 'margin_type_taxonomy_id');
    }

    /**
     * Resets Price element properties
     *
     * @return PriceElement
     */
    public function resetPrices(): PriceElement {
        $this->net_price = null;
        $this->rack_price = null;
        return $this;
    }

    /**
     * recalculate Prices based on type of price set
     *
     * @return PriceElement
     */
    public function recalculatePrices(): PriceElement {
        if (is_null($this->rack_price)) {
            $this->rack_price = $this->calculateRackPrice();
        } elseif (is_null($this->net_price)) {
            $this->net_price = $this->calculateNetPrice();
        }
        return $this;
    }

    /**
     * Recalculate price based on existing rack price
     *
     * @return float
     */
    public function calculateNetPrice(): float {
        $calc = new PriceCalculator();
        $calc->initWithRackPrice($this->rack_price, $this->margin_value, $this->margin_type_taxonomy_id);
        return $calc->getNetPrice();
    }

    /**
     * Recalculate price based on existing net price
     *
     * @return float
     * @throws Exceptions\UserException
     */
    public function calculateRackPrice(): float {
        $calc = new PriceCalculator();
        $calc->initWithNetPrice($this->net_price, $this->margin_value, $this->margin_type_taxonomy_id);
        return $calc->getRackPrice();
    }

    /**
     * Get price  element by parameters
     *
     * @param array $params parameters to search for
     * @param bool $withTrashed return with deleted elements too?
     * @return PriceElement|null
     * @static
     */
    static public function getByParameters(array $params, bool $withTrashed = false) {
        $modelMealPlanId = self::getModelMealPlanId($params['date_range_id'], $params['meal_plan']);

        $query = PriceElement
                ::where('price_id', '=', $params['price_id'])
                ->where('date_range_id', '=', $params['date_range_id'])
                ->where('model_meal_plan_id', '=', $modelMealPlanId);

        if ($withTrashed) {
            $query = $query->withTrashed($params['date_range_id']);
        }

        return $query->first();
    }

    /**
     * Get model meal plan id
     *
     * @param int $dateRangeId
     * @param string $mealPlan
     * @return int
     */
    static public function getModelMealPlanId(int $dateRangeId, string $mealPlan): int {
        $dateRange = DateRange::findOrFail($dateRangeId);
        return ModelMealPlan::findByName($mealPlan, $dateRange)->id;
    }

}
