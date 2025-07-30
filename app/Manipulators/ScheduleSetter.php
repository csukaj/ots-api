<?php

namespace App\Manipulators;

use App\Cruise;
use App\DateRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Schedule;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Manipulator to create a new DateRange
 * instance after the supplied data passes validation
 */
class ScheduleSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'from_time' => null,
        'to_time' => null,
        'cruise_id' => null,
        'frequency_taxonomy_id' => null,
        'relative_time_id' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'cruise_id' => 'required',
        'from_date' => 'required',
        'frequency' => 'required',
        'relative_time' => 'required',
    ];

    /**
     * ScheduleSetter constructor.
     * @param array $attributes
     * @throws UserException
     * @throws \Throwable
     */
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        $this->attributes['cruise_id'] = Cruise::findOrFail($attributes['cruise_id'])->id;
        $this->attributes['from_time'] = $attributes['from_date'];

        if (!empty($attributes['to_date'])) {
            $this->attributes['to_time'] = $attributes['to_date'];
        }
        $this->validateDates();

        $frequencyName = (isset($attributes['frequency']['name'])) ? $attributes['frequency']['name'] : $attributes['frequency'];
        $this->attributes['frequency_taxonomy_id'] = Taxonomy::getTaxonomy($frequencyName,
            Config::getOrFail('taxonomies.schedule_frequency'))->id;

        $relativeTime = (new RelativeTimeSetter($attributes['relative_time']))->set();
        $this->attributes['relative_time_id'] = $relativeTime->id;
    }

    /**
     * Creates new date range and throws error in case of any overlap
     * @return Schedule
     * @throws \Throwable
     */
    public function set(): Schedule
    {
        $attributes = [
            'cruise_id' => $this->attributes['cruise_id'],
            'from_time' => Schedule::getSeparationTime($this->attributes['from_time'], true),
            //because of set...Attribute
            'to_time' => Schedule::getSeparationTime($this->attributes['to_time'], false),
            //because of set...Attribute
            'frequency_taxonomy_id' => $this->attributes['frequency_taxonomy_id']
        ];
        $schedule = Schedule::createOrRestore($attributes, $this->attributes['id']);
        $schedule->fill($this->attributes)->saveOrFail();
        return $schedule;
    }

    /**
     * Validates the date range to be set
     * @throws UserException
     */
    private function validateDates()
    {
        $fromTime = DateRange::formatTime($this->attributes['from_time'], true);
        $toTime = DateRange::formatTime($this->attributes['to_time'], false);
        if ($fromTime > $toTime) {
            throw new UserException('Invalid date range: wrong date order!');
        }
    }
}
