<?php

namespace App\Entities;

use App\ContentModification;

class ContentModificationEntity extends Entity {

    protected $model;

    public function __construct(ContentModification $contentModification) {
        parent::__construct($contentModification);
    }

    public function getFrontendData(array $additions = []): array {
        return [
            "id" => $this->model->id,
            "content_id" => $this->model->content_id,
            "editor" => $this->model->editor->name,
            "modification" => $this->model->created_at
        ];
    }

}
