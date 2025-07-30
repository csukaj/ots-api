<?php

namespace Modules\Stylerstaxonomy\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modules\Stylerstaxonomy\Entities\DescriptionTranslation
 *
 * @property int $id
 * @property int $description_id
 * @property int $language_id
 * @property \Modules\Stylerstaxonomy\Entities\Description $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\Language $language
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation whereDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation withoutTrashed()
 * @mixin \Eloquent
 */
class DescriptionTranslation extends Model
{

    use SoftDeletes;

    protected $fillable = ['description_id', 'language_id', 'description'];
    protected $touches = ['description'];

    public function description(): BelongsTo
    {
        return $this->belongsTo(Description::class);
    }

    public function language(): HasOne
    {
        return $this->hasOne(Language::class, 'id', 'language_id');
    }

}
