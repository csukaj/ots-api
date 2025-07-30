<?php

namespace App\Manipulators;

use App\Facades\Config;
use App\OrganizationGroupPoi;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Manipulator to create a new Poi
 * instance after the supplied data passes validation
 */
class OrganizationGroupPoiSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes; //TODO: extend BaseSetter, but need to define attributes to filter...
    }

    /**
     * @return OrganizationGroupPoi
     * @throws \Throwable
     */
    public function set(): OrganizationGroupPoi
    {
        $attributes = [
            'type_taxonomy_id' => Taxonomy::getTaxonomy(
                $this->attributes['type'],
                Config::getOrFail('taxonomies.organization_group_poi_type')
            )->id,
            'organization_group_id'=>$this->attributes['organization_group_id'],
            'poi_id'=>(new PoiSetter($this->attributes['poi']))->set()->id
        ];
        $orgGrPoi = OrganizationGroupPoi::createOrRestore($attributes,!empty($this->attributes['id'])?$this->attributes['id']:null);

        $orgGrPoi->fill($attributes)->saveOrFail();
        return $orgGrPoi;
    }
}
