<?php

namespace Modules\Stylerscontact\Entities;

use App\Traits\ModelTrait;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modules\Stylerscontact\Entities\Person
 *
 * @property int $id
 * @property int $personable_id
 * @property string $personable_type
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylerscontact\Entities\Contact[] $contacts
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $personable
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Person forPersonable($type, $id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerscontact\Entities\Person onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Person whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Person whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Person whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Person whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Person wherePersonableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Person wherePersonableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerscontact\Entities\Person whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerscontact\Entities\Person withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerscontact\Entities\Person withoutTrashed()
 * @mixin \Eloquent
 */
class Person extends Model
{
    use SoftDeletes,
        CascadeSoftDeletes,
        ModelTrait;

    protected $fillable = ['name', 'personable_type', 'personable_id'];

    protected $cascadeDeletes = ['contacts'];

    public function personable(): MorphTo
    {
        return $this->morphTo();
    }

    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function scopeForPersonable(Builder $query, string $type, int $id): Builder
    {
        return $query
            ->where('personable_type', $type)
            ->where('personable_id', $id);
    }


}
