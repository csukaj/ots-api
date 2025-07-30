<?php

namespace App\Manipulators;

use App\Facades\Config;
use App\Poi;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

/**
 * Manipulator to create a new Poi
 * instance after the supplied data passes validation
 */
class PoiSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [];

    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
        $this->attributes = $attributes; //TODO: need to define attributes and remove this line...
    }

    /**
     * @return Poi
     * @throws \Exception
     */
    public function set(): Poi
    {
        if (!empty($this->attributes['id'])) {
            $poi = Poi::withTrashed()->findOrFail($this->attributes['id']);
            $poi->restore();
        } else {
            $poi = new Poi();
        }

        $poi->type_taxonomy_id = Taxonomy::getTaxonomy(
            $this->attributes['type'],
            Config::getOrFail('taxonomies.poi_type')
        )->id;
        $poi->name_description_id = (new DescriptionSetter(
            $this->attributes['name'],
            $poi->name_description_id
        ))->set()->id;
        $poi->description_description_id = (new DescriptionSetter(
            empty($this->attributes['description']) ? [] : $this->attributes['description'],
            $poi->description_description_id
        ))->set()->id;
        $poi->location_id = (new LocationSetter($this->attributes['location'], $poi->location_id))->set()->id;

        $poi->save();
        return $poi;
    }
}
