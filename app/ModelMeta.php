<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Modules\Stylerstaxonomy\Entities\MetaTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

abstract class ModelMeta extends Model
{
    use SoftDeletes,
        MetaTrait;

    protected $classificationClass;
    static protected $entityClass;

    /**
     * Save the model to the database.
     *
     * @param  array $options
     * @return bool
     */
    public function save(array $options = []): bool
    {
        if (is_null($this->priority)) {
            $tx = Taxonomy::findOrFail(isset($options['taxonomy_id']) ? $options['taxonomy_id'] : $this->taxonomy_id);
            $this->priority = $tx->priority;
            if (!empty($options)) {
                $options['priority'] = $this->priority;
            }
        }
        return parent::save($options);
    }


    /**
     * Relation to parent Classification
     *
     * @return HasOne
     */
    public function parentClassification(): HasOne
    {
        return $this->hasOne($this->classificationClass, 'id', 'parent_classification_id');
    }

    /**
     * Scope a query to only include listable metas.
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopeListable(Builder $query): Builder
    {
        return $query->where('is_listable', '=', true);
    }

    /**
     * Scope a query to only include searchable classifications.
     * (where taxonomy is searchable)
     *
     * @param Builder $query query to scope to
     * @return Builder
     */
    public function scopeSearchable(Builder $query): Builder
    {
        $txSearchables = Taxonomy::searchable()->pluck('id')->toArray();
        return $query->whereIn('taxonomy_id', $txSearchables);
    }

    /**
     * Scope a query to only include metas which have spacified parent.
     *
     * @param Builder $query
     * @param int $parent_classification_id parent classification id to filter for
     * @return Builder
     */
    public function scopeForParent(Builder $query, int $parent_classification_id = null): Builder
    {
        return is_null($parent_classification_id) ? $query->whereNull('parent_classification_id') : $query->where('parent_classification_id',
            '=', $parent_classification_id);
    }

    /**
     * Find specified id in list of Organization Metas
     * @param int $id
     * @param OrganizationMeta[]|Collection $orgMts
     * @return OrganizationMeta
     * @static
     */
    static public function findKeyById(int $id, $orgMts)
    {
        foreach ($orgMts as $key => $orgMt) {
            if ($orgMt->id == $id) {
                return $key;
            }
        }
        return null;
    }
}
