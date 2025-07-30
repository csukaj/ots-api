<?php

namespace Modules\Stylerscontact\Entities;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Modules\Stylerscontact\Entities\Contact
 *
 * @property int $id
 * @property int $contactable_id
 * @property string $contactable_type
 * @property int $type_taxonomy_id
 * @property string $value
 * @property string|null $extension
 * @property int|null $priority
 * @property bool $is_public
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $contactable
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $type
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact forContactable($type, $id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerscontact\Entities\Contact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact public()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereContactableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereContactableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Contact whereValue($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerscontact\Entities\Contact withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerscontact\Entities\Contact withoutTrashed()
 * @mixin \Eloquent
 */
class Contact extends Model
{

    use SoftDeletes, ModelTrait;

    protected $fillable = [
        'contactable_id',
        'contactable_type',
        'type_taxonomy_id',
        'value',
        'extension',
        'priority',
        'is_public'
    ];

    /**
     * Get all of the owning contactable models.
     */
    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation to type taxonomy
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeForContactable(Builder $query, string $type, int $id): Builder
    {
        return $query
            ->where('contactable_type', $type)
            ->where('contactable_id', $id);
    }

}
