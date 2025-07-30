<?php

namespace Modules\Stylersauth\Entities;


use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;

/**
 * Modules\Stylersauth\Entities\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $last_login
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylersauth\Entities\Role[] $roles
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersauth\Entities\User onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\User withRole($role)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersauth\Entities\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylersauth\Entities\User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements JWTSubject
{

    use SoftDeletes,
        Notifiable,
        EntrustUserTrait {
        EntrustUserTrait::save as entrustSave;
        SoftDeletes::restore as softRestore;
        EntrustUserTrait::restore as entrustRestore;
    }

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'name', 'last_login', 'password'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];
    protected $dates = ['last_login', 'deleted_at'];

    public function getEmailAttribute($value)
    {
        return strtolower($value);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Set the user's password attribute
     * @param password $value
     */
    protected function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

    /**
     * Save method - with before and after save logic
     * @param array $options
     * @return boolean
     */
    public function save(array $options = [])
    {
        if(! $this->password){
            return false;
        }
        return $this->entrustSave($options);
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @return bool|null
     */
    public function restore()
    {
        return $this->entrustRestore();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'id' => $this->id
            ]
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

}
