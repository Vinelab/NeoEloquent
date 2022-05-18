<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Model;

class BelongsToMany extends \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    public function __construct(Builder $query, Model $parent, string $relationName)
    {
        parent::__construct($query, $parent, '', '', '', '', '', $relationName);
    }

    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $table = $this->related->getTable();

            $this->whereRelation('<'.$this->relationName, $table);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    public function addEagerConstraints(array $models): void
    {
        $table = $this->related->getTable();

        $this->whereRelation('<'.$this->relationName, $table);

        parent::addEagerConstraints($models);
    }

    public function getResults()
    {
        return $this->get();
    }
}
