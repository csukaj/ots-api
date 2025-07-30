<?php

namespace App;

use App\Traits\ModelTrait;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;

/**
 * App\Review
 *
 * @mixin \Eloquent
 * @property-read \App\User $author
 * @property-read \Modules\Stylerstaxonomy\Entities\Description $description
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $reviewSubject
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|Review onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|Review withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Review withoutTrashed()
 * @property int $id
 * @property int $review_subject_id
 * @property string $review_subject_type
 * @property int $author_user_id
 * @property int $review_description_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Review whereAuthorUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Review whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Review whereReviewDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Review whereReviewSubjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Review whereReviewSubjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Review whereUpdatedAt($value)
 */
class Review extends Model
{
    use SoftDeletes, CascadeSoftDeletes, ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['review_subject_type', 'review_subject_id', 'author_user_id', 'review_description_id'];

    protected $cascadeDeletes = ['description'];

    public function reviewSubject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation to author
     *
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id', 'id');
    }

    /**
     * Relation to name description
     *
     * @return HasOne
     */
    public function description(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'review_description_id');
    }

    /**
     * @param Builder $query query to scope to
     * @param string $type
     * @param int $id
     * @return Builder
     */
    public function scopeForSubject($query, string $type, int $id): Builder
    {
        return $query
            ->where('review_subject_type', $type)
            ->where('review_subject_id', $id);
    }
}
