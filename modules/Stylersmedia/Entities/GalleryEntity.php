<?php

namespace Modules\Stylersmedia\Entities;

use App\Entities\Entity;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class GalleryEntity extends Entity
{
    protected $gallery;

    public function __construct(Gallery $gallery)
    {
        parent::__construct();

        $this->gallery = $gallery;
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->gallery->id,
            'name' => $this->gallery->name_description_id ? (new DescriptionEntity($this->gallery->name))->getFrontendData() : null,
            'galleryable_id' => $this->gallery->galleryable_id,
            'galleryable_type' => $this->gallery->galleryable_type,
            'role' => $this->gallery->role_taxonomy_id ? $this->gallery->role->name : null,
            'items' => $this->getItems(in_array('highlightedFirst', $additions))
        ];
        $galleryableName = (new $this->gallery->galleryable_type)->find($this->gallery->galleryable_id)->name;
        if ($galleryableName) {
            $return['galleryable_name'] = ($galleryableName->description) ? $galleryableName->description : $galleryableName->name;
        } else {
            $return['galleryable_name'] = null;
        }

        return $return;
    }

    protected function getItems($highlightedFirst = false)
    {
        $highlightedItems = [];
        $normalItems = [];
        foreach ($this->gallery->items()->with('file')->get() as $item) {
            if ($highlightedFirst && $item->is_highlighted) {
                $highlightedItems[] = (new FileEntity($item->file))->getFrontendData(['gallery_item']);
            } else {
                $normalItems[] = (new FileEntity($item->file))->getFrontendData(['gallery_item']);
            }
        }
        return $highlightedItems + $normalItems;
    }

    static public function getOptions()
    {
        $roleEn = new TaxonomyEntity(Taxonomy::findOrFail(Config::get('taxonomies.gallery_role')));
        $typeEn = new TaxonomyEntity(Taxonomy::findOrFail(Config::get('taxonomies.file_type')));

        return [
            'role' => $roleEn->getFrontendData(['descendants', 'translations']),
            'type' => $typeEn->getFrontendData(['descendants', 'translations'])
        ];
    }
}