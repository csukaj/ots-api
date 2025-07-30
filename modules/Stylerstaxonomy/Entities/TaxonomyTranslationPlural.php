<?php

namespace Modules\Stylerstaxonomy\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Taxonomy
 *
 * @property int $id
 * @property int $taxonomy_translation_id
 * @property int $type_taxonomy_id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\TaxonomyTranslation $taxonomyTranslation
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $typeTaxonomy
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural whereTaxonomyTranslationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural whereTypeTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural withoutTrashed()
 * @mixin \Eloquent
 */
class TaxonomyTranslationPlural extends Model
{

    use SoftDeletes;

    protected $fillable = ['taxonomy_translation_id', 'type_taxonomy_id', 'name'];

    public function taxonomyTranslation(): HasOne
    {
        return $this->hasOne(TaxonomyTranslation::class, 'id', 'taxonomy_translation_id');
    }

    public function typeTaxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'type_taxonomy_id');
    }

    static public function getTaxonomyTranslationPlural($taxonomyTrId, $typeTxId): self
    {
        return self::where([
            'taxonomy_translation_id' => $taxonomyTrId,
            'type_taxonomy_id' => $typeTxId
        ])->firstOrFail();
    }

    static public function getOrNew($taxonomyTrId, $typeTxId): self
    {
        try {
            return self::getTaxonomyTranslationPlural($taxonomyTrId, $typeTxId);
        } catch (\Exception $e) {
            $txTrPl = new self();
            $txTrPl->taxonomy_translation_id = $taxonomyTrId;
            $txTrPl->type_taxonomy_id = $typeTxId;
            return $txTrPl;
        }
    }

}
