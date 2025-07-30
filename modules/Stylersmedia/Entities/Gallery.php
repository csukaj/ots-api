<?php

namespace Modules\Stylersmedia\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Modules\Stylersmedia\Entities\Gallery
 *
 * @property int $id
 * @property int $galleryable_id
 * @property string $galleryable_type
 * @property int|null $name_description_id
 * @property int|null $role_taxonomy_id
 * @property int|null $priority
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $galleryable
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylersmedia\Entities\GalleryItem[] $items
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $name
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $role
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery forGalleryable($type, $id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\Gallery onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery whereGalleryableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery whereGalleryableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery whereNameDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery whereRoleTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersmedia\Entities\Gallery whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\Gallery withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersmedia\Entities\Gallery withoutTrashed()
 * @mixin \Eloquent
 */
class Gallery extends Model
{

    use SoftDeletes;

    protected $fillable = ['galleryable_id', 'galleryable_type', 'name_description_id', 'role_taxonomy_id', 'priority'];

    protected $touches = ['galleryable'];

    public function name(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'name_description_id');
    }

    public function role(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'role_taxonomy_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalleryItem::class, 'gallery_id', 'id')->orderBy('gallery_items.priority');
    }

    public function galleryable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include discount date ranges.
     *
     * @param Builder $query query to scope to
     * @param string $type
     * @param int $id
     * @return Builder
     */
    public function scopeForGalleryable($query, string $type, int $id): Builder
    {
        return $query
            ->where('galleryable_type', $type)
            ->where('galleryable_id', $id);
    }

}
