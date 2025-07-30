<?php

namespace App\Entities;

use App\ModelMeta;
use Modules\Stylerstaxonomy\Entities\DescriptionEntity;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ModelMetaEntity extends Entity
{
    protected $model;

    public function __construct(ModelMeta $modelMt)
    {
        parent::__construct($modelMt);
    }

    public function getFrontendData(array $additions = ['admin']): array
    {
        $return = [];
        foreach ($additions as $addition) {
            switch ($addition) {
                case 'admin':
                    $txAdditions = array_merge($additions, ['translations', 'translations_with_plurals']);
                    $taxonomyData = $this->model->taxonomy_id ? (new TaxonomyEntity($this->model->metaTaxonomy))->getFrontendData($txAdditions) : null;
                    $descriptionData = $this->model->additional_description_id ? (new DescriptionEntity($this->model->additionalDescription))->getFrontendData() : null;

                    $return = [
                        'id' => $this->model->id,
                        'taxonomy' => $taxonomyData,
                        'value' => $this->model->value,
                        'priority' => $this->model->priority,
                        'additional_description' => $descriptionData,
                        'is_listable' => $this->model->is_listable
                    ];
                    break;
                case 'frontend':
                    $taxonomyData = $this->model->taxonomy_id ? (new TaxonomyEntity($this->model->metaTaxonomy))->translations() : null;
                    $descriptionData = $this->model->additional_description_id ? (new DescriptionEntity($this->model->additionalDescription))->getFrontendData() : null;

                    $return = [
                        'name' => $taxonomyData,
                        'value' => $this->model->value,
                        'additional_description' => $descriptionData
                    ];
                    break;
            }
        }

        return $return;
    }
}
