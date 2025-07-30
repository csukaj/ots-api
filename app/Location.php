<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Location
 *
 * @property int $id
 * @property int $island_id
 * @property int $district_id
 * @property string $latitude
 * @property string $longitude
 * @property string $po_box
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read District $district
 * @property-read Island $island
 * @property-read Collection|Organization[] $organizations
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Location onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereIslandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location wherePoBox($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Location whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Location withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Location withoutTrashed()
 */
class Location extends Model
{

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['island_id', 'district_id', 'latitude', 'longitude', 'po_box'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['organizations'];

    /**
     * Relation to island
     *
     * @return HasOne
     */
    public function island(): HasOne
    {
        return $this->hasOne(Island::class, 'id', 'island_id');
    }

    /**
     * Relation to district
     *
     * @return HasOne
     */
    public function district(): HasOne
    {
        return $this->hasOne(District::class, 'id', 'district_id');
    }

    /**
     * Relation to organizations
     *
     * @return BelongsTo
     */
    public function organizations(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

}
