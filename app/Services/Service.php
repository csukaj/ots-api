<?php

namespace App\Services;

use App\Services\ErrorsTrait;

abstract class Service
{
    use ErrorsTrait;

    protected $result = [];

    public function getResult()
    {
        return $this->result;
    }
}
