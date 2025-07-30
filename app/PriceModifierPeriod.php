<?php

namespace App;

use App\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\PriceModifierPeriod
 *
 * @property int $id
 * @property int $price_modifier_id
 * @property int $date_range_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read PriceModifier $priceModifier
 * @property-read DateRange $dateRange
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierPeriod onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierPeriod whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierPeriod whereDateRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierPeriod whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierPeriod whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierPeriod wherePriceModifierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PriceModifierPeriod whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierPeriod withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\PriceModifierPeriod withoutTrashed()
 */
class PriceModifierPeriod extends Model
{

    use SoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price_modifier_id',
        'date_range_id'
    ];

    /**
     * Relation to price modifier
     *
     * @return HasOne
     */
    public function priceModifier(): HasOne
    {
        return $this->hasOne(PriceModifier::class, 'id', 'price_modifier_id');
    }

    /**
     * Relation to organization DateRange
     *
     * @return HasOne
     */
    public function dateRange(): HasOne
    {
        return $this->hasOne(DateRange::class, 'id', 'date_range_id');
    }

}
