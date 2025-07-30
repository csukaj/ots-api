<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\OrganizationManager
 *
 * @property int $id
 * @property int $organization_id
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Organization $organization
 * @property-read User $user
 * @mixin \Eloquent
 * @property string $site
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSite whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSite whereSite($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSite whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserSite whereUserId($value)
 */
class UserSite extends Model
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'site'];

    /**
     * Relation to user
     *
     * @return HasOne
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
