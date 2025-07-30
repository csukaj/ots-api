<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Modules\Stylerstaxonomy\Entities\ClassificationTrait;
use Modules\Stylerstaxonomy\Entities\Taxonomy;

abstract class ModelClassification extends Model
{
    use SoftDeletes,
        ClassificationTrait;

    protected $classificationClass;
    protected $metaClass;
    protected static $foreignKey;

    /**
     * Save the model to the database.
     * Sets priority by classification taxonomy priority
     *
     * @param  array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (is_null($this->priority)) {
            $tx = Taxonomy::findOrFail(isset($options['classification_taxonomy_id']) ? $options['classification_taxonomy_id'] : $this->classification_taxonomy_id);
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
    public function parentClassification(): BelongsTo
    {
        return $this->belongsTo($this->classificationClass, 'parent_classification_id');
    }

    /**
     * Relation to classificationTaxonomy
     *
     * @return HasOne
     */
    public function classificationTaxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'classification_taxonomy_id');
    }

    /**
     * Relation to value Taxonomy
     *
     * @return HasOne
     */
    public function valueTaxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'value_taxonomy_id');
    }

    /**
     * Relation to child Classifications
     *
     * @return HasMany
     */
    public function childClassifications(): HasMany
    {
        return $this
            ->hasMany($this->classificationClass, 'parent_classification_id', 'id')
            ->where(static::$foreignKey, $this->{static::$foreignKey})
            ->orderBy('priority');
    }

    /**
     * Relation to listable Child Classifications ordered by priority
     *
     * @return Builder
     */
    public function listableChildClassifications()
    {
        return $this->childClassifications()->listable()->orderBy('priority');
    }

    /**
     * Relation to child Metas
     *
     * @return HasMany
     */
    public function childMetas(): HasMany
    {
        return $this
            ->hasMany($this->metaClass, 'parent_classification_id', 'id')
            ->where(static::$foreignKey, $this->{static::$foreignKey})
            ->orderBy('priority');
    }

    /**
     * Relation to listable Child Metas ordered by priority
     *
     * @return HasOne
     */
    public function listableChildMetas()
    {
        return $this->childMetas()->listable()->orderBy('priority');
    }

    /**
     * Scope a query to only include listable classifications.
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
        return $query->whereIn('classification_taxonomy_id', $txSearchables);
    }

    /**
     * Scope a query to only include classifications having specified parent.
     *
     * @param Builder $query query to scope to
     * @param int $parent_classification_id parent id
     * @return Builder
     */
    public function scopeForParent(Builder $query, int $parent_classification_id = null): Builder
    {
        return is_null($parent_classification_id) ? $query->whereNull('parent_classification_id') : $query->where('parent_classification_id',
            '=', $parent_classification_id);
    }

    /**
     * Find Classification in list and returns list key of found element
     * @param int $id
     * @param Collection|ModelClassification[] $classifications
     * @return mixed
     * @static
     */
    static public function findKeyById(int $id, $classifications)
    {
        foreach ($classifications as $key => $classification) {
            if ($classification->id == $id) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Relation to charge Taxonomy
     *
     * @return BelongsTo
     */
    public function chargeTaxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class, 'charge_taxonomy_id');
    }


    /**
     * Find ModelClassification by taxonomy id at model or fail
     *
     * @param int $clTxId classification taxonomy id
     * @param int $modelId model ID
     * @return ModelClassification
     */
    static public function findByTaxonomyAndModel(int $clTxId, int $modelId)
    {
        return static::where('classification_taxonomy_id', $clTxId)
            ->where(static::$foreignKey, $modelId)
            ->firstOrFail();
    }
}
