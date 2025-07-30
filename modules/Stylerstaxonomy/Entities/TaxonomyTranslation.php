<?php

namespace Modules\Stylerstaxonomy\Entities;

use App\Facades\Config;
use Exception;
use Iatstuti\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Taxonomy
 *
 * @property int $id
 * @property int $language_id
 * @property int $taxonomy_id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Modules\Stylerstaxonomy\Entities\Language $language
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslationPlural[] $plurals
 * @property-read \Modules\Stylerstaxonomy\Entities\Taxonomy $taxonomy
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation whereLanguageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Stylerstaxonomy\Entities\TaxonomyTranslation withoutTrashed()
 * @mixin \Eloquent
 */
class TaxonomyTranslation extends Model
{

    use SoftDeletes, CascadeSoftDeletes;

    protected $fillable = ['language_id', 'taxonomy_id', 'name'];

    protected $cascadeDeletes = ['plurals'];

    public function language(): HasOne
    {
        return $this->hasOne(Language::class, 'id', 'language_id');
    }

    public function taxonomy(): HasOne
    {
        return $this->hasOne(Taxonomy::class, 'id', 'taxonomy_id');
    }

    public function plurals(): HasMany
    {
        return $this->hasMany(TaxonomyTranslationPlural::class, 'taxonomy_translation_id', 'id');
    }

    /**
     * @param $pluralsData
     * @throws \Throwable
     */
    public function updatePlurals($pluralsData)
    {
        foreach ($pluralsData as $type => $name) {
            $typeTxId = Config::getOrFail("taxonomies.languages.{$this->language->name->name}.plurals.{$type}.id");
            $plural = TaxonomyTranslationPlural::getOrNew($this->id, $typeTxId);
            $plural->name = $name;
            $plural->saveOrFail();
        }
    }

    static public function getTaxonomyTranslation($taxonomyId, $languageId): self
    {
        return self::where(['taxonomy_id' => $taxonomyId, 'language_id' => $languageId])->firstOrFail();
    }

    static public function getOrNew($taxonomyId, $languageId): self
    {
        try {
            return self::getTaxonomyTranslation($taxonomyId, $languageId);
        } catch (Exception $e) {
            $tr = new self();
            $tr->taxonomy_id = $taxonomyId;
            $tr->language_id = $languageId;
            return $tr;
        }
    }

}
