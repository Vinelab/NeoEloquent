<?php

namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope as IlluminateSoftDeletingScope;

class SoftDeletingScope extends IlluminateSoftDeletingScope implements ScopeInterface
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->whereNull($model->getQualifiedDeletedAtColumn());

        $this->extend($builder);
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     */
    public function remove(\Vinelab\NeoEloquent\Eloquent\Builder $builder, \Vinelab\NeoEloquent\Eloquent\Model $model)
    {
        $column = $model->getQualifiedDeletedAtColumn();

        $query = $builder->getQuery();

        $query->wheres = collect($query->wheres)->reject(function ($where) use ($column) {
            return $this->isSoftDeleteConstraint($where, $column);
        })->values()->all();
    }

    /**
     * Determine if the given where clause is a soft delete constraint.
     *
     * @param array  $where
     * @param string $column
     *
     * @return bool
     */
    protected function isSoftDeleteConstraint(array $where, $column)
    {
        return $where['type'] == 'Null' && $where['column'] == $column;
    }
}
