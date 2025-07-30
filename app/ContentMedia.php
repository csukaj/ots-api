<?php

namespace App;

use App\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\ContentMedia
 *
 * @property int $id
 * @property int $content_id
 * @property int $mediable_id
 * @property string $mediable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property int $media_role_taxonomy_id
 * @property-read Content $content
 * @property-read Taxonomy $mediaRoleTaxonomy
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\ContentMedia onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentMedia whereContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentMedia whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentMedia whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentMedia whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentMedia whereMediaRoleTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentMedia whereMediableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentMedia whereMediableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentMedia whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ContentMedia withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\ContentMedia withoutTrashed()
 */
class ContentMedia extends Model
{

    use SoftDeletes,
        ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content_id', 'mediable_type', 'mediable_id', 'media_role_taxonomy_id'];

    /**
     * Relation to content
     *
     * @return HasOne
     */
    public function content(): HasOne
    {
        return $this->hasOne(Content::class, 'id', 'content_id');
    }

    /**
     * Relation to media Role Taxonomy
     *
     * @return HasOne
     */
    public function mediaRoleTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'media_role_taxonomy_id');
    }

}
