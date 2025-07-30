<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Scope to order model collection by priority field
 */
class PriorityOrderScope implements Scope {

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  Builder  $builder
     * @param  Model  $model
     * @return Builder
     */
    public function apply(Builder $builder, Model $model) {
        return $builder->orderBy('priority');
    }

}
