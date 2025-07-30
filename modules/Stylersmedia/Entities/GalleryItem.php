<?php

namespace Modules\Stylersmedia\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modules\Stylersmedia\Entities\GalleryItem
 *
 * @property int $id
 * @property int $gallery_id
 * @property int $file_id
 * @property int|null $priority
 * @property bool $is_highlighted
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylersmedia\Entities\File $file
 * @property-read \Modules\Stylersmedia\Entities\Gallery $gallery
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\GalleryItem onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\GalleryItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\GalleryItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\GalleryItem whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\GalleryItem whereGalleryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\GalleryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\GalleryItem whereIsHighlighted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\GalleryItem wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\GalleryItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\GalleryItem withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\GalleryItem withoutTrashed()
 * @mixin \Eloquent
 */
class GalleryItem extends Model
{

    use SoftDeletes;

    protected $fillable = ['gallery_id', 'file_id', 'priority', 'is_highlighted'];

    protected $touches = ['gallery'];

    public function file(): HasOne
    {
        return $this->hasOne(File::class, 'id', 'file_id');
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

}
