<?php

namespace App\Manipulators;

use App\Content;
use App\DateRange;
use App\Device;
use App\DeviceMinimumNights;
use App\Exceptions\UserException;
use App\Facades\Config;

/**
 * Manipulator to create a new DeviceMinimumNights
 * instance after the supplied data passes validation
 */
class DeviceMinimumNightsSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'device_id' => null,
        'date_range_id' => null,
        'minimum_nights' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = ['id' => 'integer|nullable'];

    /**
     * DeviceMinimumNightsSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {

        parent::__construct($attributes);

        if (!isset($attributes['device_id']) || !isset($attributes['date_range_id']) || !isset($attributes['minimum_nights'])) {
            throw new UserException('Missing data for device minimum nights!');
        }

        $device = Device::find($attributes['device_id']);
        if (!$device) {
            throw new UserException('Device can not be found!');
        }

        $dateRange = DateRange::find($attributes['date_range_id']);
        if (!$dateRange || $dateRange->type_taxonomy_id != Config::get('taxonomies.date_range_types.open')) {
            throw new UserException('Open date range can not be found!');
        }

        /* @todo @ivan @20180522 ots-1667 ticket miatt ez a feltetel mar nem all fenn.
        if ($dateRange->date_rangeable_type != $device->deviceable_type || $device->deviceable_id != $dateRange->date_rangeable_id) {
            throw new UserException('Device and date range must belong to the same model!');
        }
        */
    }

    /**
     * Creates new content
     * @return Content
     * @throws \Throwable
     */
    public function set(): DeviceMinimumNights
    {
        $attributes = [
            'device_id' => $this->attributes['device_id'],
            'date_range_id' => $this->attributes['date_range_id']
        ];
        $deviceMinimumNights = DeviceMinimumNights::createOrRestore($attributes, $this->attributes['id']);
        $deviceMinimumNights->fill($this->attributes)->saveOrFail();

        return $deviceMinimumNights;
    }

}
