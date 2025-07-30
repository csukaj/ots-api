<?php

namespace App\Entities;

use App\RelativeTime;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class RelativeTimeEntity extends Entity
{

    protected $model;

    public function __construct(RelativeTime $relativeTime)
    {
        parent::__construct($relativeTime);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = [
            'id' => $this->model->id,
            'day' => $this->model->day,
            'precision' => $this->model->precision->name,
            'time_of_day' => $this->model->timeOfDayTaxonomy ? $this->model->timeOfDayTaxonomy->name : '',
            'hour' => substr($this->model->time, 0, 2),
            'time' => $this->model->time
        ];

        foreach ($additions as $addition) {
            switch ($addition) {
                case 'time_of_day_taxonomy':
                    $return['time_of_day_taxonomy'] =
                        $this->model->timeOfDayTaxonomy ?
                        (new TaxonomyEntity($this->model->timeOfDayTaxonomy))->getFrontendData(['translations']) :
                        null;
                    break;
            }
        }

        return $return;
    }
}
