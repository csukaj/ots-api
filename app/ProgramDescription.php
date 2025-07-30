<?php

namespace App;

use Carbon\Carbon;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\DescriptionTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\ProgramDescription
 *
 * @property int $id
 * @property int $program_id
 * @property int $taxonomy_id
 * @property int $description_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $deleted_at
 * @property-read Description $description
 * @property-read Taxonomy $descriptionTaxonomy
 * @property-read Program $program
 * @mixin \Eloquent
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\ProgramDescription onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramDescription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramDescription whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramDescription whereDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramDescription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramDescription whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramDescription whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProgramDescription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\ProgramDescription withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\ProgramDescription withoutTrashed()
 */
class ProgramDescription extends Model
{

    use SoftDeletes,
        CascadeSoftDeletes,
        DescriptionTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['program_id', 'taxonomy_id', 'description_id'];

    protected $cascadeDeletes = ['description'];

    /**
     * Relation to program
     *
     * @return BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
