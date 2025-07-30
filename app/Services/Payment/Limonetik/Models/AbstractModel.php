<?php

namespace App\Services\Payment\Limonetik\Models;

class AbstractModel
{
    /**
     * Get the requested class attribute - if exists
     *
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    /**
     * Set the requested class attribute - if exists
     *
     * @param $property Settable property
     * @param $value Settable property's value
     * @return mixed
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}