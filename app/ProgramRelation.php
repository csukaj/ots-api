<?php
namespace App;

use App\Program;
use App\RelativeTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\ProgramRelation
 *
 * @property int $id
 * @property int $parent_id
 * @property int $child_id
 * @property int $sequence
 * @property int $relative_time_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int|null $embarkation_type_taxonomy_id
 * @property int|null $embarkation_direction_taxonomy_id
 * @property-read \App\Program $child
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $embarkationDirection
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $embarkationType
 * @property-read \App\Program $parent
 * @property-read \App\RelativeTime $relativeTime
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation forChild($childId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation forParent($parentId)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereChildId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereEmbarkationDirectionTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereEmbarkationTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereRelativeTimeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramRelation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProgramRelation extends Model
{

    protected $fillable = [
        'id',
        'parent_id',
        'child_id',
        'sequence',
        'relative_time_id',
        'embarkation_type_taxonomy_id',
        'embarkation_direction_taxonomy_id'
    ];

    /**
     * Relation to parent program
     *
     * @return HasOne
     */
    public function parent(): HasOne
    {
        return $this->hasOne(Program::class, 'id', 'parent_id');
    }

    /**
     * Relation to child program
     *
     * @return HasOne
     */
    public function child(): HasOne
    {
        return $this->hasOne(Program::class, 'id', 'child_id');
    }

    /**
     * Relation to relative time
     *
     * @return HasOne
     */
    public function relativeTime(): HasOne
    {
        return $this->hasOne(RelativeTime::class, 'id', 'relative_time_id');
    }
    
    /**
     * Relation to relative time
     *
     * @return HasOne
     */
    public function embarkationType(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'embarkation_type_taxonomy_id');
    }
    
    /**
     * Relation to relative time
     *
     * @return HasOne
     */
    public function embarkationDirection(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'embarkation_direction_taxonomy_id');
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param Builder $query
     * @param mixed $parentId
     * @return Builder
     */
    public function scopeforParent(Builder $query, int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param Builder $query
     * @param mixed $childId
     * @return Builder
     */
    public function scopeforChild(Builder $query, int $childId): Builder
    {
        return $query->where('child_id', $childId);
    }
}
