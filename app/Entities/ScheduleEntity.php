<?php
namespace App\Entities;

use App\Schedule;
use Modules\Stylerstaxonomy\Entities\TaxonomyEntity;

class ScheduleEntity extends Entity
{

    protected $model;

    public function __construct(Schedule $schedule)
    {
        parent::__construct($schedule);
    }

    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'cruise_id' => $this->model->cruise_id,
            'from_date' => $this->model->from_time,
            'to_date' => $this->model->to_time,
            'frequency' => (new TaxonomyEntity($this->model->frequency))->getFrontendData(['translations']),
            'relative_time' => (new RelativeTimeEntity($this->model->relativeTime))->getFrontendData(['time_of_day_taxonomy']),
        ];
    }
}
