<?php

namespace App\Manipulators;

use App\District;
use App\Island;
use App\Location;

/**
 * Manipulator to create a new Location
 * instance after the supplied data passes validation
 */
class LocationSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    private $locationData;
    private $locationId;

    public function __construct(array $locationData, $locationId = null)
    {
        //TODO: use attributes array and extend basesetter
        $this->locationData = $locationData;
        $this->locationId = $locationId;
    }

    public function set(): Location
    {
        if ($this->locationId) {
            $location = Location::findOrFail($this->locationId);
        } else {
            $location = new Location();
        }

        if (empty($this->locationData['island'])) {
            $island = null;
        } elseif (is_array($this->locationData['island'])) {
            $island = Island::find($this->locationData['island']['id']);
        } else {
            $island = Island::findByName($this->locationData['island']);
        }
        $location->island_id = $island ? $island->id : null;

        if (empty($this->locationData['district'])) {
            $district = null;
        } elseif (is_array($this->locationData['district'])) {
            $district = District::find($this->locationData['district']['id']);
        } else {
            $district = District::findByName($this->locationData['district'], $island);
        }
        $location->district_id = $district ? $district->id : null;

        if (isset($this->locationData['latitude']) && isset($this->locationData['longitude'])) {
            $location->latitude = $this->locationData['latitude'];
            $location->longitude = $this->locationData['longitude'];
        }

        if (isset($this->locationData['po_box'])) {
            $location->po_box = $this->locationData['po_box'];
        }

        $location->save();
        return $location;
    }
}
