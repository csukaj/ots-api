<?php

namespace App;

use App\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\DeviceMinimumNights
 *
 * @property int $id
 * @property int $device_id
 * @property int $date_range_id
 * @property int $minimum_nights
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read DateRange $dateRange
 * @property-read Device $device
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceMinimumNights onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMinimumNights whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMinimumNights whereDateRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMinimumNights whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMinimumNights whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMinimumNights whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMinimumNights whereMinimumNights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceMinimumNights whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceMinimumNights withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceMinimumNights withoutTrashed()
 */
class DeviceMinimumNights extends Model
{

    use SoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['device_id', 'date_range_id', 'minimum_nights'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['device', 'dateRange'];

    /**
     * Relation to device
     *
     * @return HasOne
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Relation to Organization DateRange
     *
     * @return HasOne
     */
    public function dateRange(): BelongsTo
    {
        return $this->belongsTo(DateRange::class);
    }

}
