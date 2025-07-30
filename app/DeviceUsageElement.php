<?php

namespace App;

use App\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\DeviceUsageElement
 *
 * @property int $id
 * @property int $device_usage_id
 * @property int $age_range_id
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read AgeRange $ageRange
 * @property-read DeviceUsage $deviceUsage
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceUsageElement onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsageElement whereAgeRangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsageElement whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsageElement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsageElement whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsageElement whereDeviceUsageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsageElement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsageElement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceUsageElement withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceUsageElement withoutTrashed()
 */
class DeviceUsageElement extends Model
{

    use SoftDeletes, ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['device_usage_id', 'age_range_id', 'amount'];

    protected $touches = ['deviceUsage'];

    /**
     * Relation to device Usage
     *
     * @return BelongsTo
     */
    public function deviceUsage(): BelongsTo
    {
        return $this->belongsTo(DeviceUsage::class);
    }

    /**
     * Relation to Organization AgeRange
     *
     * @return HasOne
     */
    public function ageRange(): HasOne
    {
        return $this->hasOne(AgeRange::class, 'id', 'age_range_id');
    }

}
