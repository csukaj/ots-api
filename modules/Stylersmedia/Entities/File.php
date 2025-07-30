<?php

namespace Modules\Stylersmedia\Entities;

use App\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use function storage_path;

/**
 * Modules\Stylersmedia\Entities\File
 *
 * @property int $id
 * @property string|null $extension
 * @property string|null $path
 * @property int|null $type_taxonomy_id
 * @property int|null $description_id
 * @property int|null $width
 * @property int|null $height
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $description
 * @property-read \Modules\Stylersmedia\Entities\GalleryItem $galleryItem
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $type
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\File onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\File whereWidth($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\File withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\File withoutTrashed()
 * @mixin \Eloquent
 */
class File extends Model
{

    use SoftDeletes;

    protected $fillable = ['extension', 'path', 'type_taxonomy_id', 'description_id', 'width', 'height'];

    protected $touches = ['galleryItem'];

    public function type(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    public function description(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'description_id');
    }

    public function galleryItem(): BelongsTo
    {
        return $this->belongsTo(GalleryItem::class, 'id', 'file_id');
    }

    public function getAbsolutePath()
    {
        return $this->getUploadDirectory(storage_path('public'),
                Config::get('ots.media_image_dir')) . '/' . $this->path;
    }

    public function getExtension(SymfonyFile $symfonyFile = null)
    {
        return $symfonyFile ? $symfonyFile->guessExtension() : $this->extension;
    }

    public function getPath(SymfonyFile $symfonyFile = null, $thumbName = null, $absolute = false)
    {
        $root = $this->getUploadDirectory(storage_path('public'), Config::get('ots.media_image_dir')) . '/';
        $ext = $this->getExtension($symfonyFile);

        $path = sprintf('%08s', floor($this->id / 1000000) * 1000000) . '/';
        if (!is_dir($root . $path)) {
            mkdir($root . $path, 0777);
        }
        $path .= sprintf('%08s', floor($this->id / 1000) * 1000) . '/';
        if (!is_dir($root . $path)) {
            mkdir($root . $path, 0777);
        }

        $path .= sprintf('%08s', $this->file_id ? $this->file_id : $this->id);
        if (!empty($thumbName)) {
            $path .= '_' . $thumbName;
        }
        if (!empty($ext)) {
            $path .= '.' . $ext;
        }

        return ($absolute ? $root : '') . $path;
    }

    private function getUploadDirectory($root, $path)
    {
        if (!is_dir($root)) {
            mkdir($root, 0777);
        }
        $trimmedRoot = rtrim($root, '/');
        $pathParts = explode('/', trim($path, '/'));
        foreach ($pathParts as $pathPart) {
            $trimmedRoot .= '/' . $pathPart;
            if (!is_dir($trimmedRoot)) {
                mkdir($trimmedRoot, 0777);
            }
        }
        return $trimmedRoot;
    }

    public function getImageInfo()
    {
        $path = $this->getAbsolutePath();
        if (!file_exists($path)) {
            return false;
        }
        $info = getimagesize($path);
        if (!$info) {
            return false;
        }
        $return = [
            'width' => $info[0],
            'height' => $info[1],
            'type' => $info[2],
            'extension' => null,
            'mime' => $info['mime']
        ];
        switch ($return['type']) {
            case IMAGETYPE_GIF:
                $return['extension'] = 'gif';
                break;
            case IMAGETYPE_JPEG:
                $return['extension'] = 'jpg';
                break;
            case IMAGETYPE_PNG:
                $return['extension'] = 'png';
                break;
        }
        return $return;
    }

    public function isSupportedImage(): bool
    {
        $info = $this->getImageInfo();
        return $info && $info['extension'];
    }

    static public function getFromGalleries(array $galleryIds)
    {
        if (empty($galleryIds)) {
            return [];
        }
        return self
            ::join('gallery_items', 'files.id', '=', 'gallery_items.file_id')
            ->whereIn('gallery_id', $galleryIds)
            ->orderBy('priority')
            ->get();
    }

}
