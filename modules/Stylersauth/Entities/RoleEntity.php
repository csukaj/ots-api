<?php

namespace Modules\Stylersauth\Entities;

class RoleEntity {
    
    protected $role;

    public function __construct(Role $role) {
        $this->role = $role;
    }
    
    public function getFrontendData(array $additions = []) {
        $return = [
            'id' => $this->role->id,
            'name' => $this->role->name,
            'display_name' => $this->role->display_name,
            'description' => $this->role->description
        ];
        
        return $return;
    }

    static public function getCollection($roles, array $additions = []) {
        $return = [];
        foreach ($roles as $role) {
            $return[] = (new self($role))->getFrontendData($additions);
        }
        return $return;
    }
}