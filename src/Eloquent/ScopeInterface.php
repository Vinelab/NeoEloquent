<?php

namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Database\Eloquent\Scope;

interface ScopeInterface extends Scope
{
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
