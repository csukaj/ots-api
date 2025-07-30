<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\RelativeTime;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Manipulator to create a new RelativeTime
 * instance after the supplied data passes validation
 */
class RelativeTimeSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'day' => null,
        'precision' => null,
        'time_of_day' => null,
        'time_of_day_taxonomy' => null,
        'hour' => null,
        'time' => null
    ];

    /**
     * Constructs Setter and validates input data
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        if (!isset($attributes['day']) ||
            (filter_var($attributes['day'], FILTER_VALIDATE_INT) === false) ||
            $attributes['day'] < 0 ||
            $attributes['day'] > 31
        ) {
            throw new UserException('Not correct day');
        }

        if (isset($attributes['precision'])) {
            $this->attributes['precision_taxonomy_id'] = Taxonomy::getTaxonomy($attributes['precision'],
                config('taxonomies.relativetime_precision'))->id;
        } else {
            throw new UserException('Invalid or empty precision');
        }

        switch ($this->attributes['precision_taxonomy_id']) {
            case config('taxonomies.relativetime_precisions.day.id'):
                break;
            case config('taxonomies.relativetime_precisions.time_of_day.id'):
                break;
            case config('taxonomies.relativetime_precisions.hour.id'):
                if (!isset($this->attributes['hour'])) {
                    throw new UserException('Empty hour');
                }
                if ((filter_var($this->attributes['hour'], FILTER_VALIDATE_INT) === false) ||
                    $this->attributes['hour'] < 0 ||
                    $this->attributes['hour'] > 23
                ) {
                    throw new UserException('Not correct hour');
                }
                $this->attributes['time'] = $this->attributes['hour'] . ':' . '00';
                break;
            case config('taxonomies.relativetime_precisions.time.id'):
                if (!isset($this->attributes['time'])) {
                    throw new UserException('Empty time');
                }
                break;
            default:
                throw new UserException('Not implemented precision:' . $this->attributes['precision_taxonomy_id']);
                break;
        }
    }

    /**
     * Creates new Model or updates if exists
     * @return RelativeTime
     * @throws \Throwable
     */
    public function set(): RelativeTime
    {
        $attributes = [
            'day' => $this->attributes['day'],
            'precision_taxonomy_id' => $this->attributes['precision_taxonomy_id'],
            'time' => $this->attributes['time'],
            'time_of_day_taxonomy_id' => $this->handleTimeOfDayTaxonomySave()
        ];
        $relativeTime = RelativeTime::createOrRestore($attributes, $this->attributes['id']);
        $relativeTime->fill($attributes)->saveOrFail();
        return $relativeTime;
    }

    /**
     * @return mixed|null
     * @throws UserException
     */
    private function handleTimeOfDayTaxonomySave()
    {
        if ($this->attributes['precision_taxonomy_id'] != config('taxonomies.relativetime_precisions.time_of_day.id')) {
            return null;
        }
        if ($this->attributes['time_of_day_taxonomy']) { // for UI
            return Taxonomy::createOrUpdateTaxonomy($this->attributes['time_of_day_taxonomy'],
                Config::get('taxonomies.relativetime_time_of_day'))->id;
        } elseif ($this->attributes['time_of_day']) { // for Tests
            return Taxonomy::getOrCreateTaxonomy($this->attributes['time_of_day'],
                config('taxonomies.relativetime_time_of_day'))->id;
        }
        return null;
    }


}
