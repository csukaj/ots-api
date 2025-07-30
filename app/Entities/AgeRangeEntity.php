<?php

namespace App\Entities;

use App\AgeRange;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class AgeRangeEntity extends Entity
{

    protected $model;

    public function __construct(AgeRange $ageRange)
    {
        parent::__construct($ageRange);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id
        ];
        if (in_array('frontend', $additions)) {
            $name = $this->model->name->name;
            $translations = (new TaxonomyEntity($this->model->name))->getFrontendData(['translations'])['translations'];
            $translations['en'] = $name;
            $return['name'] = $translations;
        } else {
            $return = [
                'id' => $this->model->id,
                'age_rangeable_type' => $this->model->age_rangeable_type,
                'age_rangeable_id' => $this->model->age_rangeable_id,
                'from_age' => $this->model->from_age,
                'to_age' => $this->model->to_age ? $this->model->to_age : null,
                'name_taxonomy' => $this->model->name->name . ($this->model->banned ? ' (banned)' : ''),
                'banned' => $this->model->banned,
                'free' => $this->model->free
            ];
        }

        if (in_array('taxonomy', $additions)) {
            $return['taxonomy'] = (new TaxonomyEntity($this->model->name))->getFrontendData([
                'translations',
                'translations_with_plurals'
            ]);
        }

        return $return;
    }

}
