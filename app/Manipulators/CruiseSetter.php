<?php

namespace App\Manipulators;

use App\Cruise;
use App\CruiseDescription;
use App\CruiseDevice;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Location;
use App\ShipGroup;
use App\Supplier;
use App\Traits\HardcodedIdSetterTrait;
use App\Traits\PropertyCategorySetterTrait;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new cruise
 * instance after the supplied data passes validation
 */
class CruiseSetter
{
    use PropertyCategorySetterTrait, HardcodedIdSetterTrait;

    const CONNECTION_COLUMN = 'cruise_id';

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'name_description_id' => null,
        'ship_company_id' => null,
        'ship_group_id' => null,
        'itinerary_id' => null,
        'is_active' => null,
        'parent_id' => null,
        'descriptions' => null,
        'location' => null,
        'supplier_id' => null
    ];

    /**
     * Cruise properties (classfications and meta)
     *
     * @var array
     */
    private $properties = [];

    /**
     * Construct Setter and validates input data
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        //TODO: use Validator facade, and extend BaseSetter with caution to properties attribute

        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            } else {
                $this->properties[] = ['name' => $key, 'value' => $value];
            }
        }

        if (isset($attributes['name'])) {
            $this->nameDescription = $attributes['name'];
        } else {
            throw new UserException('Invalid or empty name description');
        }

        if (isset($attributes['properties'])) {
            $this->properties = $attributes['properties'];
        }

        if (!empty($attributes['supplier']) && isset($attributes['supplier']['id'])) {
            $this->attributes['supplier_id'] = Supplier::findOrFail($attributes['supplier']['id'])->id;
        } else {
            $this->attributes['supplier_id'] = null;
        }
    }

    /**
     * Create new model or updates if exists
     * @return Cruise
     */
    public function set($hardcodedId = false)
    {
        if (!$hardcodedId && $this->attributes['id']) {
            $cruise = Cruise::findOrFail($this->attributes['id']);
            $crNameDescription = (new DescriptionSetter(
                $this->nameDescription, $cruise->name_description_id
            ))->set();
            $cruise->load('name');
        } else {
            $crNameDescription = (new DescriptionSetter($this->nameDescription))->set();
            $cruise = new Cruise();
            if ($hardcodedId && $this->attributes['id']) {
                $cruise->id = $this->attributes['id'];
            }
        }

        $cruise->name_description_id = $crNameDescription->id;
        $cruise->supplier_id = $this->attributes['supplier_id'];
        if (!empty($this->attributes['ship_company_id'])) {
            $cruise->ship_company_id = $this->attributes['ship_company_id'];
        }
        if (!empty($this->attributes['ship_group_id'])) {
            $cruise->ship_group_id = $this->attributes['ship_group_id'];
        }
        if (!empty($this->attributes['itinerary_id'])) {
            $cruise->itinerary_id = $this->attributes['itinerary_id'];
        }

        if (isset($this->attributes['is_active'])) {
            $cruise->is_active = $this->attributes['is_active'];
        }

        if (empty($this->attributes['location'])) {
            $location = new Location();
            $location->saveOrFail();
            $cruise->location_id = $location->id;
        } else {
            $cruise->location_id = $this->attributes['location']['id'];
        }

        $cruise->saveOrFail();
        if ($hardcodedId && $this->attributes['id']) {
            $this->updateAutoIncrement($cruise);
        }

        if ($hardcodedId || !$this->attributes['id']) { // Do not update. Done @ orgClSetter
            $this->setPropertyCategories($cruise, !$hardcodedId);
        }

        foreach (['long_description'] as $key) {
            if (!empty($this->attributes['descriptions']) && !empty($this->attributes['descriptions'][$key])) {
                CruiseDescription::setDescription(
                    self::CONNECTION_COLUMN, $cruise->id,
                    Config::getOrFail("taxonomies.cruise_descriptions.{$key}"),
                    $this->attributes['descriptions'][$key]
                );

            }else{
                CruiseDescription::deleteDescription(self::CONNECTION_COLUMN, $cruise->id,
                    Config::getOrFail("taxonomies.cruise_descriptions.{$key}"));
            }
        }

        if (!$this->attributes['id'] && !$hardcodedId) {
            // create default (adult) age range for organization
            (new AgeRangeSetter([
                'age_rangeable_type' => Cruise::class,
                'age_rangeable_id' => $cruise->id,
                'name_taxonomy' => 'adult',
                'from_age' => 0
            ]))->set();
        }

        if (!empty($this->attributes['ship_group_id'])) {
            // create default cruiseDevices for cruise
            foreach (ShipGroup::findOrFail($cruise->ship_group_id)->devices as $device) {
                CruiseDevice::createOrRestore([
                    'cruise_id' => $cruise->id,
                    'device_id' => $device->id
                ]);
            }
        }

        return $cruise;
    }
}
