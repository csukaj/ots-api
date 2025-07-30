<?php

namespace Modules\Stylerstaxonomy\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modules\Stylerstaxonomy\Entities\Description
 *
 * @property int $id
 * @property string $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylerstaxonomy\Entities\DescriptionTranslation[] $translations
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Description onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Description whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Description whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Description whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Description whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\Description whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Description withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\Description withoutTrashed()
 * @mixin \Eloquent
 */
class Description extends Model
{

    use SoftDeletes;

    protected $fillable = ['description'];

    public function translations(): HasMany
    {
        return $this->hasMany(DescriptionTranslation::class);
    }

}
