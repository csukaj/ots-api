<?php

namespace App;

use App\Facades\Config;
use App\Manipulators\AvailabilitySetter;
use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylerstaxonomy\Entities\ClassificableTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Device
 *
 * @property int $id
 * @property int $organization_id
 * @property int $amount
 * @property int $type_taxonomy_id
 * @property int $margin_type_taxonomy_id
 * @property float $margin_value
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property int $name_taxonomy_id
 * @property-read Collection|Availability[] $availabilities
 * @property-read Collection|DeviceClassification[] $classifications
 * @property-read Collection|DeviceDescription[] $descriptions
 * @property-read Collection|Gallery[] $galleries
 * @property-read Taxonomy $marginType
 * @property-read Collection|DeviceMeta[] $metas
 * @property-read Collection|DeviceMinimumNights[] $minimumNights
 * @property-read Taxonomy $name
 * @property-read Organization $organization
 * @property-read Collection|Product[] $products
 * @property-read Taxonomy $type
 * @property-read Collection|DeviceUsage[] $usages
 * @mixin \Eloquent
 * @property int $deviceable_id
 * @property string $deviceable_type
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $deviceable
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device forDeviceable($type, $id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Device onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereDeviceableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereDeviceableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereMarginTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereNameTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Device whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Device withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Device withoutTrashed()
 */
class Device extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        ClassificableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['amount', 'type_taxonomy_id', 'name_taxonomy_id', 'margin_value'];

    protected $cascadeDeletes = [
        'usages',
        'availabilities',
        'products',
        'galleries',
        'classifications',
        'metas',
        'descriptions',
        'minimumNights'
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['deviceable'];

    protected static function boot()
    {
        parent::boot();
        static::created(function ($device) {
            $amount = (int)$device->amount * self::getAmountMultiplier($device);
            Availability::createOrUpdateInfiniteInterval(Device::class, $device->id, $amount);
        });
        static::updated(function ($device) {
            $amountDifference = ($device->amount - $device->getOriginal('amount')) * self::getAmountMultiplier($device);
            if ($amountDifference != 0) {
                (new AvailabilitySetter([
                    'availableType' => self::class,
                    'availableId' => $device->id,
                    'fromDate' => date('Y-m-d'),
                    'toDate' => null
                ]))->modify($amountDifference);
            }
        });
    }


    /**
     * Save the model to the database.
     * It assigns the margin type of organization to it,
     * and creates default (infinite) Availability interval to it.
     *
     * @param  array $options
     * @return bool
     * @throws \Throwable
     */
    public function save(array $options = [])
    {
        if (!empty($this->deviceable_id) && $this->deviceable_type == Organization::class) {
            $this->margin_type_taxonomy_id = Organization::findOrFail($this->deviceable_id)->margin_type_taxonomy_id;
        } else {
            $this->margin_type_taxonomy_id = null;
        }

        return parent::save($options);
    }

    /**
     * Relation to deviceable
     *
     * @return MorphTo
     */
    public function deviceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation to type taxonomy
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    /**
     * Relation to name taxonomy
     *
     * @return HasOne
     */
    public function name(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'name_taxonomy_id');
    }

    /**
     * Relation to DeviceUsages
     *
     * @return HasMany
     */
    public function usages(): HasMany
    {
        return $this->hasMany(DeviceUsage::class, 'device_id', 'id');
    }

    /**
     * Relation to margin Type taxonomy
     *
     * @return HasOne
     */
    public function marginType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'margin_type_taxonomy_id');
    }

    /**
     * Relation to DeviceAvailabilities
     *
     * @return MorphMany
     */
    public function availabilities(): MorphMany
    {
        return $this->morphMany(Availability::class, 'available');
    }

    /**
     * Relation to Products
     *
     * @return MorphMany
     */
    public function products(): MorphMany
    {
        return $this->morphMany(Product::class, 'productable');
    }

    /**
     * Relation to galleries
     *
     * @return MorphMany
     */
    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'galleryable');
    }

    /**
     * Relation to Device Classifications
     *
     * @return HasMany
     */
    public function classifications(): HasMany
    {
        return $this->hasMany(DeviceClassification::class, 'device_id', 'id');
    }

    /**
     * Relation to Device Metas
     *
     * @return HasMany
     */
    public function metas(): HasMany
    {
        return $this->hasMany(DeviceMeta::class, 'device_id', 'id');
    }

    /**
     * Relation to Device Descriptions
     *
     * @return HasMany
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(DeviceDescription::class, 'device_id', 'id');
    }

    /**
     * Relation to Device Minimum Nights
     *
     * @return HasMany
     */
    public function minimumNights(): HasMany
    {
        return $this->hasMany(DeviceMinimumNights::class, 'device_id', 'id');
    }

    /**
     * Scope a query to only include ...
     *
     * @param Builder $query query to scope to
     * @param string $type
     * @param int $id
     * @return Builder
     */
    public function scopeForDeviceable(Builder $query, string $type, int $id): Builder
    {
        return $query
            ->where('deviceable_type', $type)
            ->where('deviceable_id', $id);
    }

    /**
     * Check if any of the specified device names already exists
     * in specified organization.
     * (checks translations too)
     *
     * @param int $deviceableId
     * @param string $deviceableType
     * @param array $names List of names to check (translations)
     * @param int $ignoreId Device id to ignore
     * @return bool
     */
    static public function deviceNameExists(
        int $deviceableId,
        string $deviceableType,
        array $names,
        int $ignoreId = null
    ): bool
    {
        $taxonomies_query = Device
            ::select('taxonomies.name')
            ->join('taxonomies', 'devices.name_taxonomy_id', '=', 'taxonomies.id')
            ->where('deviceable_id', '=', $deviceableId)
            ->where('deviceable_type', '=', $deviceableType)
            ->whereIn('taxonomies.name', array_values($names))
            ->whereNull('taxonomies.deleted_at');
        if (!empty($ignoreId)) {
            $taxonomies_query->where('devices.id', '!=', $ignoreId);
        }

        $query = Device
            ::select('taxonomy_translations.name')
            ->join('taxonomies', 'devices.name_taxonomy_id', '=', 'taxonomies.id')
            ->join('taxonomy_translations', 'taxonomies.id', '=', 'taxonomy_translations.taxonomy_id')
            ->where('deviceable_id', '=', $deviceableId)
            ->where('deviceable_type', '=', $deviceableType)
            ->whereIn('taxonomy_translations.name', array_values($names))
            ->whereNull('taxonomies.deleted_at')
            ->whereNull('taxonomy_translations.deleted_at');
        if (!empty($ignoreId)) {
            $query->where('devices.id', '!=', $ignoreId);
        }
        $query->union($taxonomies_query);
        return (bool)count($query->get());
    }

    /**
     * @param int $accommodationId
     * @return mixed
     * @throws \Exception
     */
    static public function getDevicesChannelManagerId(int $accommodationId)
    {
        $channelManagerDeviceIdTxId = Config::getOrFail('taxonomies.device_properties.categories.settings.metas.channel_manager_id.id');
        $deviceIds = self::forDeviceable(Organization::class, $accommodationId)->pluck('id');
        return $deviceIds->count() ? DeviceMeta
            ::whereIn('device_id', $deviceIds)
            ->where('taxonomy_id', $channelManagerDeviceIdTxId)
            ->pluck('value', 'device_id')->filter()->all() : [];
    }

    static private function getAmountMultiplier(self $device)
    {
        $multiplier = 1;
        if ($device->deviceable_type == OrganizationGroup::class) {
            $multiplier *= (int)ShipGroup::findOrFail($device->deviceable_id)->getShipCount();
        }
        return $multiplier;
    }
}
