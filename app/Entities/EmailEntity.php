<?php

namespace App\Entities;

use App\Email;


class EmailEntity extends Entity
{

    protected $model;

    public function __construct(Email $email)
    {
        parent::__construct($email);
    }

    public function getFrontendData(array $additions = []): array
    {
        return [
            'id' => $this->model->id,
            'type' => $this->model->type->name,
            'subject' => ($this->model->subject) ? $this->getDescriptionWithTranslationsData($this->model->subject) : null,
            'content' => ($this->model->content) ? $this->getDescriptionWithTranslationsData($this->model->content) : null,
        ];

    }

}