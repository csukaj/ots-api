<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\ContentModification
 *
 * @property int $id
 * @property int $content_id
 * @property int $editor_user_id
 * @property string $new_content
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read \App\Content $content
 * @property-read \App\User $editor
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification byEditor($editorUserId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification forContent($contentId)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\ContentModification onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification whereContentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification whereEditorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification whereNewContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ContentModification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ContentModification withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\ContentModification withoutTrashed()
 */
class ContentModification extends Model
{

    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content_id', 'editor_user_id', 'new_content'];

    /**
     * Relation to content
     *
     * @return HasOne
     */
    public function content(): HasOne
    {
        return $this->hasOne(Content::class, 'id', 'content_id');
    }

    /**
     * Relation to editor user
     *
     * @return HasOne
     */
    public function editor(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'editor_user_id');
    }

    /**
     * Scope a query to only include modifications of specified content
     *
     * @param Builder $query query to scope to
     * @param int $contentId
     * @return Builder
     */
    public function scopeForContent(Builder $query, int $contentId): Builder
    {
        return $query->where('content_id', $contentId);
    }

    /**
     * Scope a query to only include modifications of specified user.
     *
     * @param Builder $query query to scope to
     * @param int $editorUserId
     * @return Builder
     */
    public function scopeByEditor(Builder $query, int $editorUserId): Builder
    {
        return $query->where('editor_user_id', $editorUserId);
    }

    /**
     * get CreatedAt Attribute
     *
     * @param string $date
     * @return string
     */
    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
    }

}
