<?php

namespace Modules\Stylersmedia\Manipulators;

use App\Exceptions\UserException;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Modules\Stylersmedia\Entities\File;
use Modules\Stylersmedia\Entities\FileEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Manipulators\DescriptionSetter;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

class FileSetter {

    private $attributes = [
        'id' => null,
        'extension' => null,
        'path' => null,
        'width' => null,
        'height' => null,
        'type' => null,
        'description' => null
    ];

    public function __construct(array $attributes) {
        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }
        if (isset($attributes['path'])) {
            $this->attributes['path'] = str_replace(FileEntity::getRoot(),'',$attributes['path']);
        }
    }

    public function set() {
        if ($this->attributes['id']) {
            $file = File::findOrFail($this->attributes['id']);
        } else {
            $file = new File();
        }
        $file->fill($this->attributes);

        if (!empty($this->attributes['type'])) {
            $file->type_taxonomy_id = Taxonomy::getTaxonomy($this->attributes['type'], Config::get('taxonomies.file_type'))->id;
        }

        if (!empty($this->attributes['description'])) {
            $description = (new DescriptionSetter($this->attributes['description']))->set();
            $file->description_id = $description->id;
        }else{
            $file->description_id = null;
        }

        $file->saveOrFail();

        return $file;
    }

    public function setBySymfonyFile(SymfonyFile $symfonyFile) {
        if ($symfonyFile instanceof UploadedFile && !$symfonyFile->isValid()) {
            throw new UserException($symfonyFile->getErrorMessage());
        }
        $file = new File();
        $file->save();
        try {
            $file->extension = $file->getExtension($symfonyFile);
            $file->path = $file->getPath($symfonyFile);
            $absolutePath = $file->getAbsolutePath();
            if ($symfonyFile instanceof UploadedFile) {
                $symfonyFile->move(dirname($absolutePath), basename($absolutePath));
            } else {
                if(!copy($symfonyFile->getRealPath(), $absolutePath)){
                   Throw new Exception('Failed to move file');
                }
            }
            $info = $file->getImageInfo();
            if($info){
                $file->width = $info['width'];
                $file->height = $info['height'];
            }
            $file->saveOrFail();
        } catch (Exception $e) {
            $file->forceDelete();
            throw new UserException($e->getMessage());
        }
        if ($file->isSupportedImage()) {
            self::saveScaledImages($file);
        }
        return $file;
    }
    
    static public function saveScaledImages(File $file) {
        $breakpoints = Config::get('ots.media_width_breakpoints');
        $info = $file->getImageInfo();
        
        switch ($info['type']) {
            case IMAGETYPE_GIF: $img = imagecreatefromgif($file->getAbsolutePath()); break;
            case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($file->getAbsolutePath()); break;
            case IMAGETYPE_PNG: $img = imagecreatefrompng($file->getAbsolutePath()); break;
        }
        imageinterlace($img, true);
        
        foreach ($breakpoints as $name => $width) {
            $scaledImg = imagescale($img, $width);
            $scaledPath = $file->getPath(null, $name, true);
            switch ($info['type']) {
                case IMAGETYPE_GIF: imagegif($scaledImg, $scaledPath); break;
                case IMAGETYPE_JPEG: imagejpeg($scaledImg, $scaledPath); break;
                case IMAGETYPE_PNG: imagepng($scaledImg, $scaledPath); break;
            }
            imagedestroy($scaledImg);
        }
        imagedestroy($img);
    }

}
