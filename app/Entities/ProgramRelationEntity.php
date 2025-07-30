<?php
namespace App\Entities;

use App\ProgramRelation;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ProgramRelationEntity extends Entity
{

    const MODEL_TYPE = 'program';
    const CONNECTION_COLUMN = 'program_id';

    protected $model;

    public function __construct(ProgramRelation $programRelation)
    {
        parent::__construct($programRelation);
    }

    public function getFrontendData(array $additions = []): array
    {
        if (in_array('frontend', $additions)) {
            return [
                'sequence' => $this->model->sequence,
                'relative_time' => (new RelativeTimeEntity($this->model->relativeTime))->getFrontendData(['time_of_day_taxonomy']),
                'program' => (new ProgramEntity($this->model->child))->getFrontendData(['frontend', 'galleries'])
            ];
        }

        $return = [
            'id' => $this->model->id,
            'parent_id' => $this->model->parent_id,
            'child_id' => $this->model->child_id,
            'sequence' => $this->model->sequence,
            'relative_time' => (new RelativeTimeEntity($this->model->relativeTime))->getFrontendData(['time_of_day_taxonomy']),
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'parent':
                    $return['parent'] = (new ProgramEntity($this->model->parent))->getFrontendData();
                    break;

                case 'child':
                    $return['child'] = (new ProgramEntity($this->model->child))->getFrontendData();
                    break;

                case 'embarkation':
                    $return['embarkation_type'] = $this->model->embarkationType ? (new TaxonomyEntity($this->model->embarkationType))->getFrontendData(['translations']) : null;
                    $return['embarkation_direction'] = $this->model->embarkationDirection ? (new TaxonomyEntity($this->model->embarkationDirection))->getFrontendData(['translations']) : null;
                    break;
            }
        }

        return $return;
    }
}
