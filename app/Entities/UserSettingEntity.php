<?php

namespace App\Entities;

use App\UserSetting;

class UserSettingEntity extends Entity
{
    protected $model;

    public function __construct(UserSetting $userSetting)
    {
        parent::__construct($userSetting);
    }

    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'setting' => $this->model->setting->name,
            'value' => $this->model->value->name
        ];
    }
}