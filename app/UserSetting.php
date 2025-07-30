<?php

namespace App;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\UserSetting
 *
 * @property int $id
 * @property int $user_id
 * @property int $setting_taxonomy_id
 * @property int|null $value_taxonomy_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $setting
 * @property-read \App\User $user
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $value
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\UserSetting onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSetting whereSettingTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSetting whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSetting whereValueTaxonomyId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\UserSetting withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\UserSetting withoutTrashed()
 * @mixin \Eloquent
 */
class UserSetting extends Model
{
    use SoftDeletes, ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'setting_taxonomy_id', 'value_taxonomy_id'];

    /**
     * Relation to an user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation to setting tx
     *
     * @return HasOne
     */
    public function setting(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'setting_taxonomy_id');
    }

    /**
     * Relation to setting tx
     *
     * @return HasOne
     */
    public function value(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'value_taxonomy_id');
    }
}
