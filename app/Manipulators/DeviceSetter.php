<?php

namespace App\Manipulators;

use App\Device;
use App\DeviceDescription;
use App\DeviceUsage;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\Product;
use App\Traits\HardcodedIdSetterTrait;
use Illuminate\Support\Facades\Validator;
use Modules\Stylersmedia\Manipulators\GallerySetter;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\TaxonomySetter;

/**
 * Manipulator to create a new Device 
 * instance after the supplied data passes validation
 */
class DeviceSetter extends BaseSetter
{
    use HardcodedIdSetterTrait;

    const CONNECTION_COLUMN = 'device_id';

    /**
     * Attributes that can be set from input
     * @var array 
     */
    protected $attributes = [
        'id' => null,
        'deviceable_id' => null,
        'deviceable_type' => null,
        'name' => null,
        'short_description' => null,
        'long_description' => null,
        'type' => null,
        'amount' => null,
        'margin_value' => null,
        'usages' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'name' => 'required',
        'type' => 'required',
        'amount' => 'required'
    ];

    /**
     * DeviceSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes) {

        parent::__construct($attributes);

        if (Device::deviceNameExists($this->attributes['deviceable_id'], $this->attributes['deviceable_type'], $attributes['name'], isset($this->attributes['id']) ? $this->attributes['id'] : null)) {
            throw new UserException('Room with same name exists.');
        }

        $this->attributes['name_taxonomy_id'] = (new TaxonomySetter($attributes['name'], null, Config::get('taxonomies.names.device_name')))->set()->id;
        $this->attributes['type_taxonomy_id'] = Taxonomy::getTaxonomy($attributes['type'], Config::get('taxonomies.device'))->id;

    }

    /**
     * Creates new device
     * @param bool $hardcodedId
     * @return Device
     * @throws \Throwable
     */
    public function set($hardcodedId = false): Device {

        $update = (!$hardcodedId && $this->attributes['id']);

        if ($update) {
            $device = Device::findOrFail($this->attributes['id']);
        } else {
            $device = new Device();
            $device->deviceable_id = $this->attributes['deviceable_id'];
            $device->deviceable_type = $this->attributes['deviceable_type'];
            if ($hardcodedId && $this->attributes['id']) {
                $device->id = $this->attributes['id'];
            }
        }

        $device->name_taxonomy_id = $this->attributes['name_taxonomy_id'];
        $device->type_taxonomy_id = $this->attributes['type_taxonomy_id'];
        $device->amount = $this->attributes['amount'];
        $device->margin_value = $this->attributes['margin_value'];
        $device->saveOrFail();
        if ($hardcodedId && $this->attributes['id']) {
            $this->updateAutoIncrement($device);
        }

        if (!empty($this->attributes['short_description'])) {
            DeviceDescription::setDescription(
                self::CONNECTION_COLUMN, $device->id, Config::get('taxonomies.device_descriptions.short_description'), $this->attributes['short_description']
            );
        }else{
            DeviceDescription::deleteDescription(
                self::CONNECTION_COLUMN, $device->id, Config::get('taxonomies.device_descriptions.short_description')
            );
        }
        if (!empty($this->attributes['long_description'])) {
            DeviceDescription::setDescription(
                self::CONNECTION_COLUMN, $device->id, Config::get('taxonomies.device_descriptions.long_description'), $this->attributes['long_description']
            );
        }else{
            DeviceDescription::deleteDescription(
                self::CONNECTION_COLUMN, $device->id, Config::get('taxonomies.device_descriptions.long_description')
            );
        }
        if (isset($this->attributes['usages'])) {
            $usagesToKeep = [];
            foreach ($this->attributes['usages'] as $usageData) {
                $usage = (new DeviceUsageSetter($usageData))->set($device->id);
                $usagesToKeep[] = $usage->id;
            }
            $usagesToDelete = DeviceUsage::where('device_id', $device->id)->whereNotIn('id', $usagesToKeep)->get();
            foreach($usagesToDelete as $usage){
                $usage->elements()->delete();
                $usage->delete();
            }
        }

        if (!$update) {
            // set default gallery
            $galleryAttributes = [
                'galleryable_id' => $device->id,
                'galleryable_type' => Device::class,
                'role_taxonomy_id' => Config::get('taxonomies.gallery_roles.frontend_gallery')
            ];
            (new GallerySetter($galleryAttributes))->set();
            
            // set default product
            $product = new Product();
            $product->productable_type = Device::class;
            $product->productable_id = $device->id;
            $product->type_taxonomy_id = Config::getOrFail('taxonomies.product_types.accommodation');
            $product->saveOrFail();
        }
        return $device;
    }
}