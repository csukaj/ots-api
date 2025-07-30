<?php

namespace App\Entities;

use App\AdminLog;

class AdminLogEntity extends Entity
{

    protected $model;

    public function __construct(AdminLog $adminLog)
    {
        parent::__construct($adminLog);
    }

    public function getFrontendData(array $additions = []): array
    {
        $return = $this->model->attributesToArray();
        $return['request'] = \json_decode($this->model->request);
        $return['response'] = \json_decode($this->model->response);
        $return['created_at'] = $this->model->created_at->toIso8601ZuluString();
        $return['updated_at'] = $this->model->updated_at->toIso8601ZuluString();

        return $return;
    }
}
