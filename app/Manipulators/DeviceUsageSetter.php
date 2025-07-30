<?php

namespace App\Manipulators;

use App\Availability;
use App\DeviceUsage;
use App\DeviceUsageElement;

/**
 * Manipulator to create a new DeviceUsage
 * instance after the supplied data passes validation
 */
class DeviceUsageSetter
{
    protected $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    //TODO: use Validator facade in constructor, and refactor set() to conform with other manipulators

    /**
     * Updates usage and its elements
     * @param int $deviceId
     * @return DeviceUsage
     * @throws \Throwable
     */
    public function set($deviceId = null): DeviceUsage
    {
        $deviceUsage = new DeviceUsage();
        if (!empty($this->attributes['id'])) {
            $deviceUsage = $deviceUsage->findOrFail($this->attributes['id']);
        }
        $deviceUsage->fill($this->attributes);
        if ($deviceId) {
            $deviceUsage->device_id = $deviceId;
        }
        $deviceUsage->save();

        if (isset($this->attributes['elements'])) {
            $elementsToKeep = [];
            foreach ($this->attributes['elements'] as $elementData) {
                $attributes = [
                    'device_usage_id' => $deviceUsage->id,
                    'age_range_id' => $elementData['age_range']['id'],
                    'amount' => $elementData['amount']
                ];
                $element = DeviceUsageElement::createOrRestore($attributes,!empty($elementData['id'])?$elementData['id']:null);
                $element->fill($attributes)->saveOrFail();
                $elementsToKeep[] = $element->id;
            }

            DeviceUsageElement::where('device_usage_id', $deviceUsage->id)->whereNotIn('id', $elementsToKeep)->delete();
        }

        return $deviceUsage;
    }

}
