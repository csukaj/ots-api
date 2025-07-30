<?php

namespace App\Entities;

use App\Facades\Config;
use App\ModelClassification;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\Taxonomy;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ModelClassificationEntity extends Entity
{
    protected $model;
    protected $foreignKey;
    protected $metaEntity;
    static protected $entityClass;
    static protected $classificationTaxonomyKey;

    public function __construct(ModelClassification $modelCl)
    {
        parent::__construct($modelCl);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [];
        foreach ($additions as $addition) {
            switch ($addition) {
                case 'admin':
                    $taxonomyData = $this->model->classification_taxonomy_id ? (new TaxonomyEntity($this->model->classificationTaxonomy))->getFrontendData(['translations', 'translations_with_plurals']) : null;
                    $valueData = $this->model->value_taxonomy_id ? (new TaxonomyEntity($this->model->valueTaxonomy))->getFrontendData(['translations', 'translations_with_plurals']) : null;
                    $chargeData = $this->model->charge_taxonomy_id ? (new TaxonomyEntity($this->model->chargeTaxonomy))->getFrontendData(['translations', 'translations_with_plurals']) : null;
                    $descriptionData = $this->model->additional_description_id ? (new DescriptionEntity($this->model->additionalDescription))->getFrontendData() : null;
                    $return = [
                        'id' => $this->model->id,
                        $this->foreignKey => $this->model->{$this->foreignKey},
                        'parent_classification_id' => $this->model->parent_classification_id,
                        'taxonomy' => $taxonomyData,
                        'value' => $valueData,
                        'priority' => $this->model->priority,
                        'charge' => $chargeData,
                        'additional_description' => $descriptionData,
                        'is_highlighted' => $this->model->is_highlighted,
                        'is_listable' => $this->model->is_listable,
                        'child_classifications' => static::getCollection(
                            $this->model
                                ->childClassifications()
                                ->with([
                                    'classificationTaxonomy',
                                    'valueTaxonomy',
                                    'chargeTaxonomy',
                                    'additionalDescription',
                                    'listableChildClassifications',
                                    'childMetas'
                                ])
                                ->get(),
                            $additions
                        ),
                        'child_metas' => $this->metaEntity::getCollection($this->model->childMetas()->with([
                            'metaTaxonomy',
                            'additionalDescription'
                        ])->get(), $additions)
                    ];
                    break;
                case 'frontend':
                    $taxonomyData = $this->model->classification_taxonomy_id ? (new TaxonomyEntity($this->model->classificationTaxonomy))->translations() : null;
                    $valueData = $this->model->value_taxonomy_id ? (new TaxonomyEntity($this->model->valueTaxonomy))->translations() : null;
                    $chargeData = $this->model->charge_taxonomy_id ? (new TaxonomyEntity($this->model->chargeTaxonomy))->translations() : null;
                    $descriptionData = $this->model->additional_description_id ? (new DescriptionEntity($this->model->additionalDescription))->getFrontendData() : null;
                    $return = [
                        'name' => $taxonomyData,
                        'icon' => $this->model->classificationTaxonomy->icon,
                        'value' => $valueData,
                        'charge' => $chargeData,
                        'additional_description' => $descriptionData,
                        'highlighted' => $this->model->is_highlighted,
                        'child_classifications' => static::getCollection(
                            $this->model
                                ->listableChildClassifications()
                                ->with([
                                    'classificationTaxonomy',
                                    'valueTaxonomy',
                                    'chargeTaxonomy',
                                    'additionalDescription',
                                    'listableChildClassifications',
                                    'childMetas'
                                ])
                                ->get(),
                            $additions
                        ),
                        'child_metas' => $this->metaEntity::getCollection($this->model->childMetas()->with([
                            'metaTaxonomy',
                            'additionalDescription'
                        ])->get(), $additions)
                    ];
                    break;
            }
        }
        return $return;
    }

    static public function getOptions()
    {
        $classificationEn = new TaxonomyEntity(Taxonomy::findOrFail(Config::get(static::$classificationTaxonomyKey)));
        $chargeEn = new TaxonomyEntity(Taxonomy::findOrFail(Config::get('taxonomies.charge')));

        return [
            'classification' => $classificationEn->getFrontendData(['descendants', 'translations']),
            'charge' => $chargeEn->getFrontendData(['descendants', 'translations'])
        ];
    }
}