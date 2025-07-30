<?php

namespace Modules\Stylersmedia\Entities;

use App\Entities\Entity;
use Illuminate\Support\Facades\Config;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;

class FileEntity extends Entity
{

    protected $file;

    public function __construct(File $file)
    {
        parent::__construct();

        $this->file = $file;
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->file->id,
            'extension' => $this->file->extension,
            'path' => self::getRoot() . $this->file->path,
            'thumbnails' => $this->getThumbnails(),
            'width' => $this->file->width,
            'height' => $this->file->height,
            'type' => $this->file->type_taxonomy_id ? $this->file->type->name : null,
            'description' => $this->file->description_id ? (new DescriptionEntity($this->file->description))->getFrontendData() : null
        ];

        if (in_array('gallery_item', $additions)) {
            $item = $this->file->galleryItem;
            if ($item) {
                $return['priority'] = $item->priority;
                $return['highlighted'] = $item->is_highlighted;
                $return['gallery_id'] = $item->gallery_id;
            }
        }
        return $return;
    }

    static public function getRoot()
    {
        return 'storage/' . Config::get('ots.media_image_dir') . '/';
    }

    private function getThumbnails()
    {
        $return = [];
        $breakpoints = Config::get('ots.media_width_breakpoints');

        foreach ($breakpoints as $name => $width) {
            $return[] = ['path' => $this->getRoot() . $this->file->getPath(null, $name), 'width' => $width];
        }

        return $return;
    }

}
