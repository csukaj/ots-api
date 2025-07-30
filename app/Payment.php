<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Payment
 *
 * @property int $id
 * @property int $order_id
 * @property int|null $supplier_id
 * @property string $request_id
 * @property string $payment_order_id
 * @property int|null $parent_id
 * @property string|null $status_log
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Order $Order
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $SubPayments
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment wherePaymentOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereStatusLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Payment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'payment_order_id',
        'request_id',
        'status_log'
    ];

    public function Order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function SubPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_id', 'id');
    }
}
