<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Model;

abstract class HasOneOrMany extends \Illuminate\Database\Eloquent\Relations\HasOneOrMany
{
    private string $relation;

    public function __construct(Builder $query, Model $parent, string $relation)
    {
        parent::__construct($query, $parent, '', '');
        $this->relation = $relation;
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $table = $this->related->getTable();

            $this->whereRelation($this->relation.'>', $table);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models): void
    {
        $table = $this->related->getTable();

        $this->whereRelation($this->relation.'>', $table);

        parent::addEagerConstraints($models);
    }
}
