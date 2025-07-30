<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\Organization;
use App\OrganizationGroup;
use App\OrganizationGroupDescription;
use App\ShipCompany;
use App\ShipGroup;
use App\Supplier;
use App\Traits\HardcodedIdSetterTrait;
use App\Traits\PropertyCategorySetterTrait;
use Modules\Stylersmedia\Manipulators\GallerySetter;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new organization group
 * instance after the supplied data passes validation
 */
class OrganizationGroupSetter
{
    use PropertyCategorySetterTrait, HardcodedIdSetterTrait;

    const CONNECTION_COLUMN = 'organization_group_id';

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'name_description_id' => null,
        'type_taxonomy_id' => null,
        'margin_type_taxonomy_id' => null,
        'margin_value' => null,
        'is_active' => null,
        'parent_id' => null,
        'supplier_id' => null,
        'descriptions' => null
    ];

    /**
     * Organization group properties (classfications and meta)
     *
     * @var array
     */
    private $properties = [];

    /**
     * Construct Setter and validates input data
     * @param array $attributes
     * @throws UserException
     * @throws \Exception
     */
    public function __construct(array $attributes)
    {
        //TODO: try to use basesetter
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            } else {
                $this->properties[] = ['name' => $key, 'value' => $value];
            }
        }

        if (isset($attributes['type'])) {
            $this->attributes['type_taxonomy_id'] = Taxonomy::getTaxonomy(
                $attributes['type'],
                Config::getOrFail('taxonomies.organization_group_type')
            )->id;
        } else {
            throw new UserException('Invalid or empty type');
        }

        if (isset($attributes['margin_type'])) {
            $this->attributes['margin_type_taxonomy_id'] = Taxonomy::getTaxonomy(
                $attributes['margin_type'],
                Config::getOrFail('taxonomies.margin_type')
            )->id;
        }

        if (isset($attributes['margin_value']) && is_numeric($attributes['margin_value'])) {
            $this->attributes['margin_value'] = $attributes['margin_value'];
        }

        if (!empty($attributes['parent'])) {
            if (isset($attributes['parent']['id'])) {
                $this->attributes['parent_id'] = Organization::findOrFail($attributes['parent']['id'])->id;
            } else {
                $this->attributes['parent_id'] = ShipCompany::getNames($attributes['parent'])->pluck('id')->first();
            }
        }

        if (!empty($attributes['supplier']) && isset($attributes['supplier']['id'])) {
            $this->attributes['supplier_id'] = Supplier::findOrFail($attributes['supplier']['id'])->id;
        } else {
            $this->attributes['supplier_id'] = null;
        }

        if (isset($attributes['name'])) {
            $organizationGroupClass = self::organizationGroupFactory($this->attributes['type_taxonomy_id']);

            if (!isset($attributes['id']) && count($organizationGroupClass::getNames($attributes['name']))) {
                throw new UserException('Organization group name already exists');
            } elseif (isset($attributes['id'])) {
                if (count($organizationGroupClass::getNames(array_values($attributes['name']), $attributes['id']))) {
                    throw new UserException('Organization group name already exists');
                }
            }

            $this->nameDescription = $attributes['name'];
        } else {
            throw new UserException('Invalid or empty name description');
        }

        if (isset($attributes['properties'])) {
            $this->properties = $attributes['properties'];
        }
    }

    /**
     * Create new model or updates if exists
     * @param bool $hardcodedId
     * @return OrganizationGroup
     * @throws UserException
     * @throws \Throwable
     */
    public function set($hardcodedId = false)
    {
        $organizationGroupClass = self::organizationGroupFactory($this->attributes['type_taxonomy_id']);

        if (!$hardcodedId && $this->attributes['id']) {
            $organizationGroup = $organizationGroupClass::findOrFail($this->attributes['id']);
            $orgNameDescription = (new DescriptionSetter(
                $this->nameDescription,
                $organizationGroup->name_description_id
            ))->set();
            $organizationGroup->load('name');
        } else {
            $orgNameDescription = (new DescriptionSetter($this->nameDescription))->set();
            $organizationGroup = new $organizationGroupClass();
            if ($hardcodedId && $this->attributes['id']) {
                $organizationGroup->id = $this->attributes['id'];
            }
        }

        $organizationGroup->name_description_id = $orgNameDescription->id;
        $organizationGroup->type_taxonomy_id = $this->attributes['type_taxonomy_id'];
        $organizationGroup->parent_id = $this->attributes['parent_id'];
        $organizationGroup->supplier_id = $this->attributes['supplier_id'];

        $organizationGroup->margin_type_taxonomy_id = $this->attributes['margin_type_taxonomy_id'];
        $organizationGroup->margin_value = $this->attributes['margin_value'];

        if (isset($this->attributes['is_active'])) {
            $organizationGroup->is_active = $this->attributes['is_active'];
        }

        $organizationGroup->saveOrFail();
        if ($hardcodedId && $this->attributes['id']) {
            $this->updateAutoIncrement($organizationGroup);
        }

        if ($hardcodedId || !$this->attributes['id']) { // Do not update. Done @ orgClSetter
            $this->setPropertyCategories($organizationGroup, !$hardcodedId);
        }

        foreach (['short_description', 'long_description'] as $key) {
            if (!empty($this->attributes['descriptions']) && !empty($this->attributes['descriptions'][$key])) {
                OrganizationGroupDescription::setDescription(
                    self::CONNECTION_COLUMN, $organizationGroup->id,
                    Config::getOrFail("taxonomies.organization_group_descriptions.{$key}"),
                    $this->attributes['descriptions'][$key]
                );

            } else {
                OrganizationGroupDescription::deleteDescription(self::CONNECTION_COLUMN, $organizationGroup->id,
                    Config::getOrFail("taxonomies.organization_group_descriptions.{$key}"));
            }
        }

        if (!$this->attributes['id']) {
            if (!$hardcodedId) {
                // create default (adult) age range for organization
                (new AgeRangeSetter([
                    'age_rangeable_type' => OrganizationGroup::class,
                    'age_rangeable_id' => $organizationGroup->id,
                    'name_taxonomy' => 'adult',
                    'from_age' => 0
                ]))->set();
            }
            // create default gallery for organization
            (new GallerySetter([
                'galleryable_id' => $organizationGroup->id,
                'galleryable_type' => ShipGroup::class,
                'role_taxonomy_id' => Config::getOrFail('taxonomies.gallery_roles.frontend_gallery')
            ]))->set();

            // set default product
            $product = (new ProductSetter([
                'productable_type' => ShipGroup::class,
                'productable_id' => $organizationGroup->id,
                'type_taxonomy_id' => Config::getOrFail('taxonomies.product_types.accommodation'),
                'name_description' => ['en' => 'default']
            ]))->set();
            $nameTaxonomy = Taxonomy::getOrCreateTaxonomy('default', Config::get('taxonomies.names.price_name'));
            (new PriceSetter([
                'product_id' => $product->id,
                'name_taxonomy_id' => $nameTaxonomy->id,
                'extra' => false,
                'mandatory' => true,
            ]))->set();
        }

        return $organizationGroup;
    }

    static protected function organizationGroupFactory(string $type)
    {
        switch ($type) {
            case Config::getOrFail('taxonomies.organization_group_types.ship_group.id'):
                return ShipGroup::class;
                break;
        }
        throw new UserException('Unknown organization group type');
    }

}
