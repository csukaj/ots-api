<?php

namespace App\Manipulators;

use App\Accommodation;
use App\Device;
use App\Exceptions\UserException;
use App\Facades\Config;
use App\HotelChain;
use App\Location;
use App\Organization;
use App\OrganizationDescription;
use App\Ship;
use App\ShipCompany;
use App\ShipGroup;
use App\Supplier;
use App\Traits\HardcodedIdSetterTrait;
use App\Traits\PropertyCategorySetterTrait;
use Modules\Stylersmedia\Manipulators\GallerySetter;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new Organization
 * instance after the supplied data passes validation
 */
class OrganizationSetter
{

    use PropertyCategorySetterTrait, HardcodedIdSetterTrait;

    const CONNECTION_COLUMN = 'organization_id';

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'name_description_id' => null,
        'type_taxonomy_id' => null,
        'is_active' => null,
        'descriptions' => null,
        'location' => null,
        'parentable_type' => null,
        'parentable_id' => null,
        'supplier_id' => null
    ];

    /**
     * Organization properties (classfications and meta)
     *
     * @var array
     */
    private $properties = [];
    private $organizationClass;
    private $nameDescription;
    private $shortNameDescription;

    /**
     * Construct Setter and validates input data
     * @param array $attributes
     * @throws UserException
     * @throws \Exception
     */
    public function __construct(array $attributes)
    {

        //TODO: try to use BaseSetter
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            } else {
                $this->properties[] = ['name' => $key, 'value' => $value];
            }
        }

        if (isset($attributes['type'])) {
            $this->attributes['type_taxonomy_id'] = Taxonomy::getTaxonomy(
                $attributes['type'], Config::getOrFail('taxonomies.organization_type')
            )->id;
        } else {
            throw new UserException('Invalid or empty type');
        }

        $this->organizationClass = self::organizationFactory($this->attributes['type_taxonomy_id']);
        $parentClassName = $this->getParentClassName($this->attributes['type_taxonomy_id']);

        if (!empty($attributes['parent'])) {
            $this->attributes['parentable_type'] = $parentClassName;
            $this->attributes['parentable_id'] = (isset($attributes['parent']['id'])) ? $parentClassName::findOrFail($attributes['parent']['id'])->id : $parentClassName::getNames($attributes['parent'])->pluck('id')->first();
        }

        if (!empty($attributes['supplier']) && isset($attributes['supplier']['id'])) {
            $this->attributes['supplier_id'] = Supplier::findOrFail($attributes['supplier']['id'])->id;
        } else {
            $this->attributes['supplier_id'] = null;
        }

        if (isset($attributes['name'])) {
            if (!isset($attributes['id']) && count($this->organizationClass::getNames($attributes['name']))) {
                throw new UserException('Organization name already exists');
            } elseif (isset($attributes['id'])) {
                if (count($this->organizationClass::getNames(array_values($attributes['name']), $attributes['id']))) {
                    throw new UserException('Organization name already exists');
                }
            }

            $this->nameDescription = $attributes['name'];
        } else {
            throw new UserException('Invalid or empty name description');
        }

        if (!empty($attributes['short_name'])) {
            if (!isset($attributes['id']) && count($this->organizationClass::getNames($attributes['short_name']))) {
                throw new UserException('Organization short name already exists');
            } elseif (isset($attributes['id'])) {
                if (count($this->organizationClass::getNames(array_values($attributes['short_name']),
                    $attributes['id']))) {
                    throw new UserException('Organization short name already exists');
                }
            }

            $this->shortNameDescription = $attributes['short_name'];
        }

        if (isset($attributes['properties'])) {
            $this->properties = $attributes['properties'];
        }
    }

    /**
     * Create new model or updates if exists
     * @param bool $hardcodedId Hardcode id on seed
     * @return Organization
     * @throws UserException
     * @throws \Exception
     * @throws \Throwable
     */
    public function set($hardcodedId = false)
    {
        $orgShortNameDescription = null;
        if (!$hardcodedId && $this->attributes['id']) {
            $organization = $this->organizationClass::findOrFail($this->attributes['id']);
            $orgNameDescription = (new DescriptionSetter(
                $this->nameDescription, $organization->name_description_id
            ))->set();
            $organization->load('name');
            if ($this->shortNameDescription) {
                $orgShortNameDescription = (new DescriptionSetter(
                    $this->shortNameDescription, $organization->short_name_description_id
                ))->set();
            }
        } else {
            $orgNameDescription = (new DescriptionSetter($this->nameDescription))->set();
            $organization = new $this->organizationClass();
            if ($hardcodedId && $this->attributes['id']) {
                $organization->id = $this->attributes['id'];
            }
            if ($this->shortNameDescription) {
                $orgShortNameDescription = (new DescriptionSetter($this->shortNameDescription))->set();
            }
        }

        $organization->name_description_id = $orgNameDescription->id;
        $organization->short_name_description_id = ($orgShortNameDescription) ? $orgShortNameDescription->id : null;
        $organization->type_taxonomy_id = $this->attributes['type_taxonomy_id'];

        if (isset($this->attributes['is_active'])) {
            $organization->is_active = $this->attributes['is_active'];
        }

        if (empty($this->attributes['location'])) {
            $location = new Location();
            $location->saveOrFail();
            $organization->location_id = $location->id;
        } else {
            $organization->location_id = $this->attributes['location']['id'];
        }

        $organization->parentable_type = isset($this->attributes['parentable_type']) ? $this->attributes['parentable_type'] : null;
        $organization->parentable_id = isset($this->attributes['parentable_id']) ? $this->attributes['parentable_id'] : null;
        $organization->supplier_id = $this->attributes['supplier_id'];

        $organization->saveOrFail();
        if ($hardcodedId && $this->attributes['id']) {
            $this->updateAutoIncrement($organization);
        }

        if ($hardcodedId || !$this->attributes['id']) { // Do not update. Done @ orgClSetter
            $this->setPropertyCategories($organization, !$hardcodedId);
        }

        foreach (['short_description', 'long_description'] as $key) {
            if (!empty($this->attributes['descriptions']) && !empty($this->attributes['descriptions'][$key])) {
                OrganizationDescription::setDescription(
                    self::CONNECTION_COLUMN, $organization->id,
                    Config::getOrFail("taxonomies.organization_descriptions.{$key}"),
                    $this->attributes['descriptions'][$key]
                );

            } else {
                OrganizationDescription::deleteDescription(self::CONNECTION_COLUMN, $organization->id,
                    Config::getOrFail("taxonomies.organization_descriptions.{$key}"));
            }
        }

        if (!$this->attributes['id']) {
            if (!$hardcodedId) {
                // create default (adult) age range for organization
                (new AgeRangeSetter([
                    'age_rangeable_type' => Organization::class,
                    'age_rangeable_id' => $organization->id,
                    'name_taxonomy' => 'adult',
                    'from_age' => 0
                ]))->set();
            }
            // create default gallery for organization
            (new GallerySetter([
                'galleryable_id' => $organization->id,
                'galleryable_type' => Organization::class,
                'role_taxonomy_id' => Config::getOrFail('taxonomies.gallery_roles.frontend_gallery')
            ]))->set();
        }
        if (!$hardcodedId && !$this->attributes['id']) {
            $this->updateAvailabilities($organization);
        }
        $organization->touch();
        return $organization;
    }

    /**
     * Update availability of ship groups devices on ship creation
     * @param $organization
     * @throws \Exception
     * @throws \Throwable
     */
    protected function updateAvailabilities($organization)
    {
        if ($this->organizationClass != Ship::class) {
            return;
        }
        $devices = $organization->parentable->devices;
        foreach ($devices as $device) {
            (new AvailabilitySetter([
                    'availableType' => Device::class,
                    'availableId' => $device->id,
                    'fromDate' => date('Y-m-d'),
                    'toDate' => null
                ]
            ))->modify($device->amount);
        }
    }

    /**
     * @param string $type
     * @return string
     * @throws UserException
     * @throws \Exception
     */
    static protected function organizationFactory(string $type)
    {
        switch ($type) {
            case Config::getOrFail('taxonomies.organization_types.accommodation.id'):
                return Accommodation::class;
            case Config::getOrFail('taxonomies.organization_types.hotel_chain.id'):
                return HotelChain::class;
            case Config::getOrFail('taxonomies.organization_types.ship_company.id'):
                return ShipCompany::class;
            case Config::getOrFail('taxonomies.organization_types.ship.id'):
                return Ship::class;
            case Config::getOrFail('taxonomies.organization_types.supplier.id'):
                return Supplier::class;
            default:
                throw new UserException('Unknown organization type');
        }

    }

    /**
     * @param string $type
     * @return null|string
     * @throws UserException
     * @throws \Exception
     */
    public function getParentClassName(string $type)
    {

        switch ($type) {
            case Config::getOrFail('taxonomies.organization_types.accommodation.id'):
                return HotelChain::class;
            case Config::getOrFail('taxonomies.organization_types.ship.id'):
                return ShipGroup::class;
            case Config::getOrFail('taxonomies.organization_types.hotel_chain.id'):
            case Config::getOrFail('taxonomies.organization_types.ship_company.id'):
            case Config::getOrFail('taxonomies.organization_types.supplier.id'):
                return null;
            default:
                throw new UserException('Unknown organization type');
        }
    }
}
