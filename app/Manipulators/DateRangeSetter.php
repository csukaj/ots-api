<?php

namespace App\Manipulators;

use App\DateRange;
use App\Exceptions\UserException;
use App\Facades\Config;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new DateRange
 * instance after the supplied data passes validation
 */
class DateRangeSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'date_rangeable_type' => null,
        'date_rangeable_id' => null,
        'from_time' => null,
        'to_time' => null,
        'type_taxonomy_id' => null,
        'margin_value' => null,
        'meal_plans' => null,
        'minimum_nights' => null
    ];
    private $openOnlyAttributes = [
        'margin_value' => null,
        'meal_plans' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = ['id' => 'integer|nullable'];

    public function __construct(array $attributes)
    {
        parent::__construct($attributes);

        if (isset($attributes['from_date'])) {
            $this->attributes['from_time'] = $attributes['from_date'];
        }

        if (isset($attributes['to_date'])) {
            $this->attributes['to_time'] = $attributes['to_date'];
        }

        if (isset($attributes['type'])) {
            $this->attributes['type_taxonomy_id'] = Taxonomy::getTaxonomy($attributes['type'],
                Config::getOrFail('taxonomies.date_range_type'))->id;
        }

        if (isset($attributes['name'])) {
            $this->nameDescription = $attributes['name'];
        }

        if (isset($attributes['minimum_nights'])) {
            if (!is_numeric($attributes['minimum_nights']) || $attributes['minimum_nights'] < 0) {
                throw new UserException("Minimum nights must be integer.");
            }
        }

        if ($this->attributes['type_taxonomy_id'] == Config::getOrFail('taxonomies.date_range_types.closed')) {
            $this->attributes = array_merge($this->attributes, $this->openOnlyAttributes);
        }

        $this->validate(); //TODO: use validator facade
    }

    /**
     * Creates new date range and throws error in case of any overlap
     * @return DateRange
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     */
    public function set(): DateRange
    {

        $attributes = [
            'date_rangeable_type' => $this->attributes['date_rangeable_type'],
            'date_rangeable_id' => $this->attributes['date_rangeable_id'],
            'from_time' => DateRange::getSeparationTime($this->attributes['from_time'], true),
            'to_time' => DateRange::getSeparationTime($this->attributes['to_time'], false),
            'type_taxonomy_id' => $this->attributes['type_taxonomy_id']
        ];
        $dateRange = DateRange::createOrRestore($attributes, $this->attributes['id']);
        $dateRange->fill($this->attributes);

        if (!is_null($this->nameDescription)) {
            $nameDescription = (new DescriptionSetter($this->nameDescription, $dateRange->name_description_id))->set();
            $dateRange->name_description_id = $nameDescription->id;
        }

        $dateRange->saveOrFail();

        if (!is_null($this->attributes['meal_plans'])) {
            $dateRange->setMealPlans($this->attributes['meal_plans']);
        }

        return $dateRange;
    }

    /**
     * Validates the date range to be set
     * @throws UserException
     * @throws \Exception
     */
    private function validate()
    {
        $fromTime = DateRange::formatTime($this->attributes['from_time'], true);
        $toTime = DateRange::formatTime($this->attributes['to_time'], false);
        if ($fromTime > $toTime) {
            throw new UserException('Invalid date range: wrong date order!');
        }

        $isPriceModifier = ($this->attributes['type_taxonomy_id'] == Config::getOrFail('taxonomies.date_range_types.price_modifier'));
        $dateRangesInInterval = DateRange::getDateRangesInInterval(
            $this->attributes['date_rangeable_type'],
            $this->attributes['date_rangeable_id'],
            $this->attributes['from_time'],
            $this->attributes['to_time'],
            $this->attributes['type_taxonomy_id'],
            $this->attributes['id']
        );

        if (!$isPriceModifier && count($dateRangesInInterval) > 0) {
            throw new UserException('Date range overlap.');
        }

        if ($isPriceModifier) {
            $dateRangesInInterval = DateRange::getDateRangesInInterval(
                $this->attributes['date_rangeable_type'],
                $this->attributes['date_rangeable_id'],
                $this->attributes['from_time'],
                $this->attributes['to_time'],
                Config::getOrFail('taxonomies.date_range_types.open'),
                null
            );

            if (count($dateRangesInInterval) == 0) {
                throw new UserException('Missing open period for price modifier.');
            }
        }
    }

}
