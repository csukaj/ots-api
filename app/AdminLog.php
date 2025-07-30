<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\AdminLog
 *
 * @property int $id
 * @property int $user_id
 * @property string $path
 * @property string $action
 * @property string $request
 * @property string $response
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog forRoute($path)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog forUser($id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog whereRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog whereResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\AdminLog whereUserId($value)
 * @mixin \Eloquent
 */
class AdminLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'path', 'action', 'request', 'response'];

    /**
     * Relation to user
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include records for specific user.
     *
     * @param Builder $query query to scope to
     * @param int $id
     * @return Builder
     */
    public function scopeForUser(Builder $query, int $id): Builder
    {
        return $query->where('user_id', $id);
    }

    /**
     * Scope a query to only include records for a specific path.
     *
     * @param Builder $query query to scope to
     * @param string $path
     * @return Builder
     */
    public function scopeForRoute(Builder $query, string $path): Builder
    {
        return $query->where('path', $path);
    }
}
