<?php

namespace Modules\Stylersauth\Entities;

use Zizaco\Entrust\EntrustRole;

/**
 * Modules\Stylersauth\Entities\Role
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylersauth\Entities\Permission[] $perms
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylersauth\Entities\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Role whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends EntrustRole
{
    
}