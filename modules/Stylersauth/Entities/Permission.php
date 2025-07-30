<?php
namespace Modules\Stylersauth\Entities;

use Zizaco\Entrust\EntrustPermission;

/**
 * Modules\Stylersauth\Entities\Permission
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylersauth\Entities\Role[] $roles
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Permission whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Permission whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylersauth\Entities\Permission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Permission extends EntrustPermission
{
}