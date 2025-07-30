<?php

namespace App;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\DeviceUsage
 *
 * @property int $id
 * @property int $device_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Device $device
 * @property-read Collection|DeviceUsageElement[] $elements
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceUsage onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsage whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\DeviceUsage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceUsage withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\DeviceUsage withoutTrashed()
 */
class DeviceUsage extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['device_id'];

    protected $cascadeDeletes = ['elements'];

    protected $touches = ['device'];

    /**
     * Relation to device
     *
     * @return BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Relation to DeviceUsage Elements
     *
     * @return HasMany
     */
    public function elements(): HasMany
    {
        return $this->hasMany(DeviceUsageElement::class, 'device_usage_id', 'id')->orderBy('id');
    }

}
