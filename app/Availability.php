<?php

namespace App;

use App\Facades\Config;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

/**
 * App\Availability
 * 
 * No soft delete...
 *
 * @property int $id
 * @property string $available_type
 * @property int $available_id
 * @property string $from_time
 * @property string $to_time
 * @property int $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Device $device
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $available
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability forAvailable($availableType, $availableId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereAvailableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereAvailableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereFromTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereToTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Availability whereUpdatedAt($value)
 */
class Availability extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['available_type', 'available_id', 'from_time', 'to_time', 'amount'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['available'];

    /* Relation to device
     * 
     * @return HasOne
     */

    public function available(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * set FromTime Attribute
     *
     * @param string $date
     * @throws Exception
     */
    public function setFromTimeAttribute($date)
    {
        $this->attributes['from_time'] = $this->getSeparationTime($date);
    }

    /**
     * get FromTime Attribute
     * @param string $time
     * @return string
     */
    public function getFromTimeAttribute($time)
    {
        return substr($time, 0, 10);
    }

    /**
     * set ToTime Attribute
     * @param string $date
     * @throws Exception
     */
    public function setToTimeAttribute($date)
    {
        $this->attributes['to_time'] = $this->getSeparationTime($date);
    }

    /**
     * get ToTime Attribute
     * @param string $time
     * @return string
     */
    public function getToTimeAttribute($time)
    {
        return $time ? substr($time, 0, 10) : null;
    }

    public function scopeForAvailable(Builder $query, string $availableType, int $availableId): Builder
    {
        return $query->where('available_type', $availableType)->where('available_id', $availableId);
    }

    /**
     * Adds separation time to a date
     *
     * @param string|null $date
     * @return string|null
     * @throws Exception
     */
    static public function getSeparationTime($date)
    {
        if (is_null($date)) {
            return null;
        }
        if (strlen($date) > 10) {
            return $date;
        }
        return $date . ' ' . Config::getOrFail('ots.midday_separation_time');
    }

    /**
     * Returns all availabilities of a device
     *
     * @param string $availableType
     * @param int $availableId
     * @return Collection
     * @static
     */
    static public function getAll(string $availableType, int $availableId)
    {
        return self::forAvailable($availableType, $availableId)->orderBy('from_time')->get();
    }

    /**
     * Get Availabilities for a device In Interval specified by fromdate and todate
     *
     * @param string $availableType
     * @param int $availableId
     * @param string $fromDate
     * @param string $toDate
     * @return array|Collection|null
     * @throws Exception
     */
    static public function getAvailabilitiesInInterval(
        string $availableType,
        int $availableId,
        string $fromDate,
        string $toDate
    ) {

        if (is_null($availableType)) {
            throw new Exception('Invalid argument for available type.');
        }
        if (is_null($availableId)) {
            throw new Exception('Invalid argument for available id.');
        }
        if (is_null($fromDate)) {
            throw new Exception('Invalid argument for from date.');
        }
        if (is_null($toDate)) {
            throw new Exception('Invalid argument for to date.');
        }

        $fromTime = self::getSeparationTime($fromDate);
        $toTime = self::getSeparationTime($toDate);

        $query1 = self
            ::forAvailable($availableType, $availableId)
            ->where('from_time', '<=', $fromTime)
            ->where(function ($query) use ($toTime) {
                $query
                    ->orWhere('to_time', '>=', $toTime)
                    ->orWhere('to_time', '=', null);
            });

        $query2 = self
            ::forAvailable($availableType, $availableId)
            ->where('from_time', '>=', $fromTime)
            ->where('from_time', '<=', $toTime);

        $query3 = self
            ::forAvailable($availableType, $availableId)
            ->whereNotNull('to_time')
            ->where(function ($query) use ($fromTime, $toTime) {
                $query
                    ->where('to_time', '>', $fromTime)
                    ->where('to_time', '<=', $toTime);
            });

        return $query3->union($query2)->union($query1)->orderBy('from_time')->get();
    }

    /**
     * Get availabilities from given date to infinity
     *
     * @param string $availableType
     * @param int $availableId
     * @param string $fromDate
     * @param bool $includeOverlapping
     * @return Collection|null
     * @throws Exception
     * @static
     */
    static public function getAvailabilitiesToInfinity(string $availableType, int $availableId, string $fromDate, bool $includeOverlapping=false)
    {
        $fromTime = self::getSeparationTime($fromDate);
        $overlapping = new Collection();
        if($includeOverlapping){
            $overlapping = self
                ::forAvailable($availableType, $availableId)
                ->where('from_time', '<', $fromTime)
                ->where(function (Builder $query) use ($fromTime) {
                    $query
                        ->where('to_time', '>=', $fromTime)
                        ->orWhereNull('to_time');
                })
                ->get();
        }
        $toInfinity = self
            ::forAvailable($availableType, $availableId)
            ->where('from_time', '>=', $fromTime)
            ->get();
        return ($overlapping->isNotEmpty())? $overlapping->concat($toInfinity): $toInfinity;
    }

    /**
     * Checks if a model has Availabilities in interval specified by fromtime and totime
     *
     * @param string $availableType
     * @param int $availableId
     * @param string $fromTime
     * @param string $toTime
     * @return bool
     */
    static public function hasAvailabilities(
        string $availableType,
        int $availableId,
        string $fromTime = null,
        string $toTime = null
    ): bool {

        $avQuery = self
            ::forAvailable($availableType, $availableId)
            ->where(function ($query) use ($fromTime, $toTime) {
                if (!is_null($fromTime)) {
                    $query->orWhere(function ($query) use ($fromTime) {
                        $query->where('from_time', '<=', $fromTime);
                        $query->whereRaw('(to_time > ? OR to_time IS NULL)', [$fromTime]);
                    });
                }
                if (!is_null($toTime)) {
                    $query->orWhere(function ($query) use ($toTime) {
                        $query->where('from_time', '<', $toTime);
                        $query->where('to_time', '>=', $toTime);
                    });
                }
                if (!is_null($fromTime) && !is_null($toTime)) {
                    $query->orWhere(function ($query) use ($fromTime, $toTime) {
                        $query->where('from_time', '<', $fromTime);
                        $query->where('to_time', '>=', $toTime);
                    });
                    $query->orWhere(function ($query) use ($fromTime, $toTime) {
                        $query->where('from_time', '>=', $fromTime);
                        $query->where('to_time', '<', $toTime);
                    });
                }
            });
        return $avQuery->exists();
    }

    /**
     * Get infinite interval that includes specified date (if any)
     *
     * @param string $availableType
     * @param int $availableId
     * @param string $fromTime
     * @return Availability|null
     * @static
     */
    static public function getOverallInfiniteInterval(string $availableType, int $availableId, string $fromTime)
    {
        return self
            ::forAvailable($availableType, $availableId)
            ->where('from_time', '<=', $fromTime)
            ->whereNull('to_time')
            ->first();
    }


    /**
     * Get interval that includes specified date (if any)
     *
     * @param string $availableType
     * @param int $availableId
     * @param string $fromTime
     * @return Availability|null
     */
    static public function getStartInterval(string $availableType, int $availableId, string $fromTime)
    {
        return self
            ::forAvailable($availableType, $availableId)
            ->where('from_time', '<=', $fromTime)
            ->where('to_time', '>', $fromTime)
            ->first();
    }

    /**
     * Get interval that includes specified date
     * or is an infinite interval (if any)
     *
     * @param string $availableType
     * @param int $availableId
     * @param string $toTime
     * @return Availability|null
     * @static
     */
    static public function getEndInterval(string $availableType, int $availableId, string $toTime)
    {
        return self
            ::forAvailable($availableType, $availableId)
            ->where('from_time', '<=', $toTime)
            ->where(function ($query) use ($toTime) {
                $query
                    ->orWhere('to_time', '>=', $toTime)
                    ->orWhere('to_time', '=', null);
            })
            ->first();
    }

    /**
     * Creates new infinite interval for a model from today to infinity and beyond with specified amount.
     * If the infinite interval already exists, updates it.
     *
     * @param string $availableType
     * @param int $availableId
     * @param int $amount
     * @static
     * @throws \Throwable
     */
    static public function createOrUpdateInfiniteInterval(string $availableType, int $availableId, int $amount)
    {
        $availability = self
            ::forAvailable($availableType, $availableId)
            ->whereNull('to_time')
            ->first();

        if (!$availability) {
            $availability = new self;
            $availability->available_type = $availableType;
            $availability->available_id = $availableId;
            $availability->from_time = self::getSeparationTime(date('Y-m-d'));
        }

        $availability->amount = $amount;
        $availability->saveOrFail();
    }

}
