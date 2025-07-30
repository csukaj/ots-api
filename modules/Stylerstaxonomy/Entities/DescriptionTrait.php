<?php

namespace Modules\Stylerstaxonomy\Entities;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

trait DescriptionTrait
{

    public function descriptionTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id');
    }

    public function description(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'description_id');
    }

    static public function setDescription(string $columnName, int $objectId, int $taxonomyId, $descriptionData)
    {
        $object = self
            ::where($columnName, $objectId)
            ->where('taxonomy_id', $taxonomyId)->first();

        if ($object) {
            $description = (new DescriptionSetter($descriptionData, $object->description_id))->set();
        } else {
            $description = (new DescriptionSetter($descriptionData))->set();

            $object = new self();
            $object->{$columnName} = $objectId;
            $object->taxonomy_id = $taxonomyId;
            $object->description_id = $description->id;
            $object->save();
        }
        return $description;
    }

    static public function deleteDescription(string $columnName, int $objectId, int $taxonomyId)
    {
        $description = self::where($columnName, $objectId)->where('taxonomy_id', $taxonomyId)->first();
        if ($description) {
            $description->delete();
        }
    }
}
