<?php

namespace App;

use App\Facades\Config;
use App\Traits\ModelTrait;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\CruiseDevice
 *
 * @property int $id
 * @property int $cruise_id
 * @property int $device_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Cruise $cruise
 * @property-read \App\Device $device
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Price[] $prices
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Product[] $products
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\CruiseDevice onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDevice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDevice whereCruiseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDevice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDevice whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDevice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CruiseDevice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\CruiseDevice withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\CruiseDevice withoutTrashed()
 * @mixin \Eloquent
 */
class CruiseDevice extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cruise_id', 'device_id'];

    protected $cascadeDeletes = ['products'];

    /**
     * @param array $options
     * @return bool
     * @throws \Throwable
     */
    public function save(array $options = []): bool
    {
        $success = parent::save($options);
        if ($success) {
            Product::createOrRestore([
                'productable_type' => CruiseDevice::class,
                'productable_id' => $this->id,
                'type_taxonomy_id' => Config::getOrFail('taxonomies.product_types.accommodation')
            ]);
        }
        return $success;
    }


    /**
     * Relation to name description
     *
     * @return BelongsTo
     */
    public function cruise(): BelongsTo
    {
        return $this->belongsTo(Cruise::class);
    }

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
     * Relation to prices
     *
     * @return HasManyThrough
     */
    public function prices(): HasManyThrough
    {
        //TODO: check if we need age_rangeable_type
        return $this->hasManyThrough(
            Price::class, AgeRange::class, 'age_rangeable_id', 'age_range_id', 'id'
        );
    }

    /**
     * Relation to products
     *
     * @return MorphMany
     */
    public function products(): MorphMany
    {
        return $this->morphMany(Product::class, 'productable');
    }
}
