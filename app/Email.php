<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Stylerstaxonomy\Entities\Description;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

/**
 * App\Email
 *
 * @property int $id
 * @property int $type_taxonomy_id
 * @property int $subject_description_id
 * @property int $content_description_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Taxonomy $type
 * @property-read Description $content
 * @property-read Description $subject
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereContentDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereSubjectDescriptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Email whereUpdatedAt($value)
 */
class Email extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type_taxonomy_id',
        'subject_description_id',
        'content_description_id'
    ];

    /**
     * Relation to status Taxonomy
     *
     * @return HasOne
     */
    public function type(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    /**
     * Relation to subject Description
     *
     * @return HasOne
     */
    public function subject(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'subject_description_id');
    }

    /**
     * Relation to content Description
     *
     * @return HasOne
     */
    public function content(): HasOne
    {
        return $this->hasOne(Description::class, 'id', 'content_description_id');
    }


}
