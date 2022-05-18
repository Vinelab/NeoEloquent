<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Model;

class BelongsToMany extends \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    public function __construct(Builder $query, Model $parent, string $relationName)
    {
        parent::__construct($query, $parent, '', '', '', $parent->getKeyName(), '', $relationName);
    }
    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $this->basicConstraints();
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    public function addEagerConstraints(array $models): void
    {
        $this->basicConstraints();
    }

    public function associate($model): \Illuminate\Database\Eloquent\Model
    {
        if ($model instanceof Model) {
            $this->related->setRelation('<'.$this->relationName, $model);
        } else {
            $this->related->unsetRelation('<'.$this->relationName);
        }

        return $this->parent;
    }


    /**
     * @return Model
     */
    public function dissociate(): Model
    {
        return $this->parent->setRelation($this->relationName.'>', null);
    }

    public function getResults()
    {
        return $this->get();
    }

    /**
     * @return void
     */
    private function basicConstraints(): void
    {
        // We need to swap around the corresponding nodes, as the processor will otherwise load the wrong node into the models
        $table = $this->parent->getTable();

        $oldFrom = $this->query->from;
        $this->query->from($table)
            ->crossJoin($oldFrom);

        $this->query->whereRelationship($this->relationName . '>', $oldFrom);
    }
}
