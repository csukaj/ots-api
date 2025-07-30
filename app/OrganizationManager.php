<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationManager onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationManager whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationManager whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationManager whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationManager whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationManager whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OrganizationManager whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationManager withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\OrganizationManager withoutTrashed()
 */
class OrganizationManager extends Model
{

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['organization_id', 'user_id'];

    /**
     * Relation to organization
     *
     * @return HasOne
     */
    public function organization(): HasOne
    {
        return $this->hasOne(Organization::class, 'id', 'organization_id');
    }

    /**
     * Relation to user
     *
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
