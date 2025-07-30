<?php

namespace App\Manipulators;

use App\Exceptions\UserException;
use App\Facades\Config;
use App\Schedule;
use App\User;
use App\UserSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * Manipulator to create a new DateRange
 * instance after the supplied data passes validation
 */
class UserSettingSetter extends BaseSetter
{

    /**
     * Attributes that can be set from input
     * @var array
     */
    protected $attributes = [
        'id' => null,
        'user_id' => null,
        'setting_taxonomy_id' => null,
        'value_taxonomy_id' => null
    ];

    /**
     * Model Validation rules for Validator
     */
    protected $rules = [
        'user_id' => ['required', 'numeric'],
        'setting' => 'required',
        'value' => 'required'
    ];

    /**
     * UserSettingSetter constructor.
     * @param array $attributes
     * @throws UserException
     */
    public function __construct(array $attributes)
    {
        $this->rules['user_id'][] = Rule::in(Auth::id());

        parent::__construct($attributes);

        $this->attributes['user_id'] = User::findOrFail($attributes['user_id'])->id;

        $this->attributes['setting_taxonomy_id'] = Taxonomy::getTaxonomy($attributes['setting'],
            Config::getOrFail('taxonomies.user_setting'))->id;
        $this->attributes['value_taxonomy_id'] = Taxonomy::getTaxonomy($attributes['value'],
            $this->attributes['setting_taxonomy_id'])->id;

    }

    /**
     * Creates new date range and throws error in case of any overlap
     * @return Schedule
     * @throws \Throwable
     */
    public function set(): UserSetting
    {
        $attributes = [
            'user_id' => $this->attributes['user_id'],
            'setting_taxonomy_id' => $this->attributes['setting_taxonomy_id'],
        ];
        $userSetting = UserSetting::createOrRestore($attributes, $this->attributes['id']);
        $userSetting->fill($this->attributes)->saveOrFail();
        return $userSetting;
    }

}
