<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\Organization;
use App\User;
use App\UserSite;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Stylersauth\Entities\Role;

/**
 * Manipulator to create a new User
 * instance after the supplied data passes validation
 */
class UserSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'name' => null,
        'email' => null,
        'password' => null,
        'roles' => null,
        'organizations' => null,
        'sites' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'email' => ['required', 'email'],
        'name' => 'required|between:3,80|regex:/^[\pP\pL\s\-]+$/u',
        //'password' => 'required', // not required for update
        'roles' => 'required'
    ];

    /**
     * Constructs Setter and validates input data
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        $emailRule = Rule::unique('users','email')
            ->where(function (Builder $query) {
                $query->whereNull('deleted_at');
            });
        if (!empty($attributes['id'])) {
            $emailRule->ignore($attributes['id']);
        }
        $this->rules['email'][] = $emailRule;
        $this->rules['sites.*'] = Rule::in(array_keys(Config::getOrFail('ots.site_languages')));

        parent::__construct($attributes);

        if (in_array('manager', $attributes['roles']) && empty($attributes['organizations'])) {
            throw new UserException('Organizations must be defined for managers');
        }

        foreach ($attributes as $key => $value) {
            if (array_key_exists($key, $this->attributes)) {
                $this->attributes[$key] = $value;
            }
        }

    }

    /**
     * Creates new Model or updates if exists
     * @return User
     */
    public function set(): User
    {
        //TODO: use modeltrait...
        if ($this->attributes['id']) {
            $user = User::withTrashed()->findOrFail($this->attributes['id']);
        } else {
            $user = User::withTrashed()->firstOrCreate(['email' => $this->attributes['email'] ]);
        }
        if ($user->trashed()) {
            $user->restore();
        }

        $user->name = $this->attributes['name'];
        $user->email = $this->attributes['email'];

        if (!empty($this->attributes['password'])) {
            $user->password = $this->attributes['password']; // hashing is done @ Modules\Stylersauth\Entities\User::setPasswordAttribute
        }

        try{
            $user->saveOrFail();
        }catch (QueryException $exception){
            if ($exception->getCode() == 23505){
                throw new UserException('There is a deleted user with this email address. It you need to use this address please create new user to reenable.');
            }
        }

        if ($this->attributes['roles'] !== null) {
            $roles = Role::whereIn('name', $this->attributes['roles'])->get();
            $user->roles()->sync($roles);

            if (in_array('manager', $this->attributes['roles'])) {
                $organizations = Organization::whereIn('id', $this->attributes['organizations'])->get();
                $user->organizations()->sync($organizations);
            } else {
                if (in_array('admin', $this->attributes['roles'])) {
                    $user->organizations()->sync([]);
                }
            }
        }

        if (isset($this->attributes['sites'])) {
            $sites = $this->attributes['sites'];
            $userSites = UserSite::where('user_id', $user->id)->get();
            $idsToDel = $userSites->filter(function ($userSite) use ($sites) {
                return !in_array($userSite->site, $sites);
            })->pluck('id')->toArray();
            if (!empty($idsToDel)) {
                UserSite::destroy($idsToDel);
            }
            $data = [];
            foreach (array_diff($sites, $userSites->pluck('site')->toArray()) as $siteToAdd) {
                $data[] = ['user_id' => $user->id, 'site' => $siteToAdd];
            }
            if (!empty($data)) {
                UserSite::insert($data);
            }
        }

        return $user;
    }

}
