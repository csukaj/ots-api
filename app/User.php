<?php

namespace App;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylersauth\Entities\Role;
use Modules\Stylersauth\Entities\User as StylersUser;

/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property string $last_login
 * @property-read Collection|Organization[] $organizations
 * @property-read Collection|Role[] $roles
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Accommodation[] $accommodations
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\UserSetting[] $settings
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\UserSite[] $sites
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User forSite($site)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\User onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User withRole($role)
 * @method static \Illuminate\Database\Query\Builder|\App\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\User withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Review[] $reviews
 */
class User extends StylersUser
{

    use SoftDeletes,
        CascadeSoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $cascadeDeletes = ['settings'];

    public function getEmailAttribute($value)
    {
        return strtolower($value);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * organizations
     * Relation to users Organization models
     *
     * @return BelongsToMany
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_managers', 'user_id', 'organization_id');
    }

    /**
     * organizations
     * Relation to users Organization models
     *
     * @return BelongsToMany
     */
    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class, 'organization_managers', 'user_id', 'organization_id');
    }

    /**
     * roles
     * Relation to users Role Models
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * roles
     * Relation to users Role Models
     *
     * @return BelongsToMany
     */
    public function sites(): HasMany
    {
        return $this->hasMany(UserSite::class);
    }

    /**
     * hasRole
     * Returns if user has the specified role
     *
     * @param string $name Role Name
     *
     * @return bool
     */
    public function hasRole($name, $requireAll = false)
    {
        return boolval($this->roles()->where('roles.name', $name)->count());
    }

    /**
     * hasOrganization
     * Returns if user has the specified organization
     *
     * @param int $organizationId
     *
     * @return bool
     */
    public function hasOrganization(int $organizationId): bool
    {
        return boolval($this->organizations()->where('organizations.id', $organizationId)->count());
    }

    /**
     * @return HasMany
     */
    public function settings(): HasMany
    {
        return $this->hasMany(UserSetting::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class,'author_user_id');
    }

    static public function scopeForSite(Builder $query, string $site): Builder
    {
        return $query->whereHas('sites', function ($query) use ($site) {
            $query->where('site', $site);
        });
    }


}
