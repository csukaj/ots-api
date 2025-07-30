<?php

namespace App\Entities;

use App\User;
use Modules\Stylersauth\Entities\RoleEntity;

class UserEntity extends Entity
{

    protected $model;

    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id,
            'email' => $this->model->email,
            'name' => $this->model->name
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'roles':
                    $return['roles'] = RoleEntity::getCollection($this->model->roles);
                    break;
                case 'organizations':
                    $return['organizations'] = OrganizationEntity::getCollection($this->model->organizations);
                    break;
                case 'sites':
                    $return['sites'] = $this->model->sites->pluck('site')->toArray();
                    break;
            }
        }

        return $return;
    }
}