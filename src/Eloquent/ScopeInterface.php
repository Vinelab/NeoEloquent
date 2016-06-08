<?php

namespace Vinelab\NeoEloquent\Eloquent;

interface ScopeInterface
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Builder  $builder
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Builder  $builder
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $model
     *
     * @return void
     */
    public function remove(Builder $builder, Model $model);
}
