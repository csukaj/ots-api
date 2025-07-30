<?php

namespace App\Traits;

/**
 * This trait is used for additional model behaviours
 */
trait ModelTrait
{

    /**
     * Returns all fillable attributes with value
     *
     * @return array
     */
    public function getFillableAttributes()
    {
        $fillable = $this->getFillable();
        return array_filter(
            $this->toArray(), function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Creates or restores a model with specified attributes. Or with id.
     *
     * @param mixed $attributesToFindWith
     * @param int|null $id id if exists
     * @return \static
     * @throws \Throwable
     */
    static public function createOrRestore($attributesToFindWith, $id = null)
    {

        if ($id) {
            $model = static::withTrashed()->findOrFail($id);
        } else {
            $model = static::withTrashed()->firstOrCreate($attributesToFindWith);
        }
        if ($model->trashed()) {
            $model->restore();
        }
        return $model;
    }

}
