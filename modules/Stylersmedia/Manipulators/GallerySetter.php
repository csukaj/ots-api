<?php

namespace Modules\Stylersmedia\Manipulators;

use App\Exceptions\UserException;
use Illuminate\Support\Facades\Config;
use Modules\Stylersmedia\Entities\Gallery;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;

class GallerySetter
{
    private $attributes = [
        'id' => null,
        'galleryable_id' => null,
        'galleryable_type' => null,
        'name_description_id' => null,
        'role_taxonomy_id' => null,
        'priority' => null
    ];

    public function __construct(array $attributes) {
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }
    }


    /**
     * Creates new gallery and throws error in case of any overlap
     * @return Gallery
     * @throws UserException
     */
    public function set() {
        if (isset($this->attributes['priority']) && $this->priorityExists($this->attributes['galleryable_id'], $this->attributes['galleryable_type'], $this->attributes['priority'])) {
            throw new UserException('Priority already exists.');
        }

        if ($this->attributes['id']) {
            $gallery = Gallery::findOrFail($this->attributes['id']);
        } else {
            $gallery = new Gallery();
        }
        $gallery->fill($this->attributes);
        
        if (!empty($this->attributes['description'])) {
            $description = (new DescriptionSetter($this->attributes['description']))->set();
            $gallery->name_description_id = $description->id;
        }
        
        if (!empty($this->attributes['role'])) {
            $gallery->role_taxonomy_id = Taxonomy::getTaxonomy($this->attributes['role'], Config::get('taxonomies.gallery_role'))->id;
        }
        
        $gallery->saveOrFail();
        
        return $gallery;
    }

    private function priorityExists($galleryableId, $galleryableType, $priority) {
        $count = Gallery
            ::where('galleryable_id', '=', $galleryableId)
            ->where('galleryable_type', '=', $galleryableType)
            ->where('priority', '=', $priority)
            ->count();
        return ($count > 0);
    }
}