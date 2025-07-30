<?php

namespace App;

use App\Traits\ModelTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Product
 *
 * @property int $id
 * @property int $day
 * @property int $precision_taxonomy_id
 * @property time $time
 * @property-read Taxonomy $precision
 * @property-read Taxonomy $time_of_day
 * @mixin \Eloquent
 * @property int|null $time_of_day_taxonomy_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $timeOfDayTaxonomy
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\RelativeTime onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelativeTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelativeTime whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelativeTime whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelativeTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelativeTime wherePrecisionTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelativeTime whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelativeTime whereTimeOfDayTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelativeTime whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\RelativeTime withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\RelativeTime withoutTrashed()
 */
class RelativeTime extends Model {

    use SoftDeletes, ModelTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['day', 'precision_taxonomy_id', 'time_of_day_taxonomy_id', 'time'];

    /**
     * precision
     * Relation to precision taxonomy
     *
     * @return HasOne
     */
    public function precision() {
        return $this->hasOne(Taxonomy::class, 'id', 'precision_taxonomy_id');
    }

    /**
     * time_of_day_taxonomy
     * Relation to time_of_day taxonomy
     *
     * @return HasOne
     */
    public function timeOfDayTaxonomy() {
        return $this->hasOne(Taxonomy::class, 'id', 'time_of_day_taxonomy_id');
    }
}
