<?php

namespace App;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\OrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property int $device_id
 * @property string $from_date
 * @property string $to_date
 * @property int $amount
 * @property int $meal_plan_id
 * @property int $order_itemable_index
 * @property float $price
 * @property string $json
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Device $device
 * @property-read Collection|OrderItemGuest[] $guests
 * @property-read MealPlan $mealPlan
 * @property-read Order $order
 * @mixin \Eloquent
 * @property int $order_itemable_id
 * @property string $order_itemable_type
 * @property float|null $margin
 * @property int $tax
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $orderItemable
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\OrderItem onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereMealPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereOrderItemableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereOrderItemableIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereOrderItemableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\OrderItem withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\OrderItem withoutTrashed()
 */
class OrderItem extends Model
{

    private $jsonDecoded;

    use SoftDeletes,
        CascadeSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'order_itemable_type',
        'order_itemable_id',
        'from_date',
        'to_date',
        'amount',
        'meal_plan_id',
        'order_itemable_index',
        'price',
        'margin',
        'json',
        'tax'
    ];

    protected $cascadeDeletes = ['guests'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['orderItemable'];

    /**
     * Relation to order
     *
     * @return HasOne
     */
    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    /**
     * @return MorphTo
     */
    public function orderItemable(): MorphTo
    {
        return $this->morphTo();
    }


    /**
     * Relation to mealPlan
     *
     * @return HasOne
     */
    public function mealPlan()
    {
        return $this->hasOne(MealPlan::class, 'id', 'meal_plan_id');
    }

    /**
     * Relation to Order Item Guest
     *
     * @return HasMany
     */
    public function guests()
    {
        return $this->hasMany(OrderItemGuest::class, 'order_item_id', 'id');
    }

    public function getMode(): string
    {
        $mode = '';
        switch ($this->order_itemable_type) {
            case Device::class:
                $mode = ($this->productableType() == Cruise::class) ? 'Cruise' : 'Accommodation';
                break;
            case ShipGroup::class:
                $mode = 'Charter';
                break;
        }
        return $mode;
    }

    public function getJSON()
    {
        if (is_null($this->jsonDecoded)) {
            $this->jsonDecoded = \json_decode($this->json);
        }
        return ($this->jsonDecoded) ? (array)$this->jsonDecoded : [];
    }

    public function getFromJSON(string $field)
    {
        if (is_null($this->jsonDecoded)) {
            $this->jsonDecoded = \json_decode($this->json);
        }
        return ($this->jsonDecoded && isset($this->jsonDecoded->$field)) ? $this->jsonDecoded->$field : null;
    }

    public function compulsoryFee()
    {
        return $this->getFromJSON(__FUNCTION__);
    }

    public function optionalFees()
    {
        return $this->getFromJSON(__FUNCTION__);
    }

    public function productableType()
    {
        return $this->getFromJSON(__FUNCTION__);
    }

    public function productableModel()
    {
        return $this->getFromJSON(__FUNCTION__);
    }

    public function unitGrossPrice()
    {
        $price = $this->price;

        if ($this->tax != 0) {
            $price = $price * (1 + $this->tax / 100);
        }

        return $price;
    }

    public function unitTax()
    {
        return $this->price * $this->tax / 100;
    }

    public function sumTax()
    {
        # amount valojaban a quantity
        return $this->amount * $this->unitTax();
    }

    public function sumNetPrice()
    {
        return $this->price * $this->amount;
    }

    public function sumGrossPrice()
    {
        return $this->amount * $this->unitGrossPrice();
    }
}
