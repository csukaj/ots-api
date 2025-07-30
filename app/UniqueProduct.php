<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\UniqueProduct
 *
 * @property int $id
 * @property int $supplier_id
 * @property int $cart_id
 * @property string $name
 * @property string $unit
 * @property string|null $from_date
 * @property string|null $to_date
 * @property int $amount
 * @property float $net_price
 * @property float $margin
 * @property float $tax
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Cart $cart
 * @property-read \App\Supplier $supplier
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\UniqueProduct onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereNetPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UniqueProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UniqueProduct withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\UniqueProduct withoutTrashed()
 * @mixin \Eloquent
 */
class UniqueProduct extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'cart_id',
        'name',
        'unit',
        'from_date',
        'to_date',
        'amount',
        'net_price',
        'margin',
        'tax',
        'description'
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
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

    public function unitPrice()
    {
        $price = $this->net_price;

        if ($this->tax != 0) {
            $price = $price * (1 + $this->tax / 100);
        }

        return $price;
    }

    public function sumPrice()
    {
        return $this->unitPrice() * $this->amount;
    }
}
