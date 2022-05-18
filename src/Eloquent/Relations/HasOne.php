<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Concerns\CanBeOneOfMany;
use Illuminate\Database\Eloquent\Relations\Concerns\ComparesRelatedModels;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Query\JoinClause;
use Vinelab\NeoEloquent\Eloquent\Model;

class HasOne extends HasOneOrMany
{
    use ComparesRelatedModels, CanBeOneOfMany, SupportsDefaultModels;

    /**
     * Get the results of the relationship.
     *
     * @return Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getResults()
    {
        return $this->query->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array  $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array  $models
     * @param  Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation): array
    {
        return $this->matchOne($models, $results, $relation);
    }

    /**
     * Add the constraints for an internal relationship existence query.
     *
     * Essentially, these queries compare on column names like "whereColumn".
     *
     * @param Builder $query
     * @param Builder $parentQuery
     * @param  array|mixed  $columns
     * @return Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*']): Builder
    {
        if ($this->isOneOfMany()) {
            $this->mergeOneOfManyJoinsTo($query);
        }

        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }

    /**
     * Add constraints for inner join subselect for one of many relationships.
     *
     * @param Builder $query
     * @param  string|null  $column
     * @param  string|null  $aggregate
     * @return void
     */
    public function addOneOfManySubQueryConstraints(Builder $query, $column = null, $aggregate = null): void
    {
        $query->addSelect($this->foreignKey);
    }

    /**
     * Get the columns that should be selected by the one of many subquery.
     *
     * @return array|string
     */
    public function getOneOfManySubQuerySelectColumns()
    {
        return $this->foreignKey;
    }

    /**
     * Add join query constraints for one of many relationships.
     *
     * @param  JoinClause $join
     * @return void
     */
    public function addOneOfManyJoinSubQueryConstraints(JoinClause $join): void
    {
        $join->on($this->qualifySubSelectColumn($this->foreignKey), '=', $this->qualifyRelatedColumn($this->foreignKey));
    }

    /**
     * Make a new related instance for the given model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $parent
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newRelatedInstanceFor(\Illuminate\Database\Eloquent\Model $parent): \Illuminate\Database\Eloquent\Model
    {
        return $this->related->newInstance();
    }

    /**
     * Get the value of the model's foreign key.
     *
     * @param Model $model
     *
     * @return mixed
     */
    protected function getRelatedKeyFrom(Model $model)
    {
        return $model->getAttribute($this->getForeignKeyName());
    }
}
