<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\OrderItemGuest
 *
 * @property int $id
 * @property int $order_item_id
 * @property int $guest_index
 * @property int $age_range_id
 * @property string $first_name
 * @property string $last_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read AgeRange $ageRange
 * @property-read OrderItem $orderItem
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\OrderItemGuest onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereAgeRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereGuestIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereOrderItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrderItemGuest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\OrderItemGuest withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\OrderItemGuest withoutTrashed()
 */
class OrderItemGuest extends Model {

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_item_id', 'guest_index', 'age_range_id', 'first_name', 'last_name'
    ];

    /**
     * Relation to order item
     * 
     * @return HasOne
     */
    public function orderItem(): HasOne {
        return $this->hasOne(OrderItem::class, 'id', 'order_item_id');
    }

    /**
     * Relation to age range
     * 
     * @return HasOne
     */
    public function ageRange(): HasOne {
        return $this->hasOne(AgeRange::class, 'id', 'age_range_id');
    }

}
