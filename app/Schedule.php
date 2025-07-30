<?php

namespace App;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Schedule
 *
 * @property int $id
 * @property int $cruise_id
 * @property string $from_time
 * @property string $to_time
 * @property int $frequency_taxonomy_id
 * @property int|null $relative_time_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\Cruise $cruise
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $frequency
 * @property-read \App\RelativeTime $relativeTime
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule forCruise($cruiseId)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Schedule onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCruiseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereFrequencyTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereFromTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereRelativeTimeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereToTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Schedule withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Schedule withoutTrashed()
 * @mixin \Eloquent
 */
class Schedule extends Model
{

    use SoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cruise_id',
        'from_time',
        'to_time',
        'frequency_taxonomy_id',
        'relative_time_id'
    ];

    /**
     * Relation to a ship company
     *
     * @return BelongsTo
     */
    public function cruise(): BelongsTo
    {
        return $this->belongsTo(Cruise::class, 'cruise_id');
    }

    /**
     * Relation to frequency taxonomy
     *
     * @return HasOne
     */
    public function frequency(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'frequency_taxonomy_id');
    }

    /**
     * Relation to margin type taxonomy
     *
     * @return HasOne
     */
    public function relativeTime(): HasOne
    {
        return $this->hasOne(RelativeTime::class, 'id', 'relative_time_id');
    }

    /**
     * Relation to margin type taxonomy
     *
     * @param Builder $query
     * @param int $cruiseId
     * @return Builder
     */
    public function scopeForCruise(Builder $query, int $cruiseId): Builder
    {
        return $query->where('cruise_id', $cruiseId);
    }


    /**
     * set FromTime Attribute with separation time
     *
     * @param string $date
     */
    public function setFromTimeAttribute(string $date)
    {
        $this->attributes['from_time'] = $this->getSeparationTime($date, true);
    }

    /**
     * get FromTime Attribute from separated time
     *
     * @param string $time
     * @return string
     */
    public function getFromTimeAttribute(string $time): string
    {
        return substr($time, 0, 10);
    }

    /**
     * set toTime Attribute with separation time
     *
     * @param string $date
     */
    public function setToTimeAttribute(string $date)
    {
        $this->attributes['to_time'] = $this->getSeparationTime($date, false);
    }

    /**
     * get toTime Attribute from separated time
     *
     * @param string $time
     * @return string
     */
    public function getToTimeAttribute(string $time): string
    {
        return $time ? substr($time, 0, 10) : null;
    }

    /**
     * Adds separation time to a date
     *
     * @param string $dateOrTime
     * @param bool $isFrom
     * @return string
     */
    static public function getSeparationTime(string $dateOrTime, bool $isFrom): string
    {
        if (is_null($dateOrTime) || strlen($dateOrTime) > 10) {
            return $dateOrTime;
        }
        return $dateOrTime . ' ' . ($isFrom ? '0:00:00' : '23:59:59');
    }
}
